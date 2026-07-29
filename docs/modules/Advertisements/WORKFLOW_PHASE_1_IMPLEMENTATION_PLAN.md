# Workflow Phase 1: Generic Infrastructure Implementation Plan

**Status**: 📋 Historical reference
**Date**: July 19, 2026
**Target Completion**: July 22, 2026

> This document is retained as historical context for the refactor from the legacy advertisement-specific workflow approach to the current generic workflow engine.

---

## 🎯 Phase 1 Objective

Transform the Advertisement-specific Workflow Engine into a **generic, database-driven, reusable Business Process Management system** that can handle:
- Advertisement Approval
- KYC Verification
- Electronic Contracts
- Withdrawal Requests
- Financial Documents
- Disputes & Arbitration
- Complaints
- Bank Employee Workflows
- Future Business Processes

**Key Constraint**: Core Workflow Engine must NOT contain any entity-specific logic.

---

## 📊 Current Architecture Analysis

### Files to Keep (Working Well)
```
✅ Modules/Advertisements/Listeners/AdvertisementWorkflowListeners.php
   - Generic listeners (can be reused)
   
✅ Modules/Advertisements/DTO/BaseDTO.php
   - Generic DTO base class
   
✅ Modules/Advertisements/Policies/AdvertisementPolicy.php
   - Authorization policies
   
✅ Database tables:
   - advertisement_logs (activity logging)
   - advertisement_workflow_audits (audit trail)
   - advertisement_workflow_states (configuration)
   - advertisement_workflow_transitions (configuration)
```

### Files to Refactor (Core Components)
```
⚠️  Services/Workflow/WorkflowEngine.php
    - Has hardcoded match statements
    - Advertisement-specific methods
    - Needs to become generic
    
⚠️  Services/Workflow/WorkflowStateManager.php
    - Reads config, not database
    - Needs to use new WorkflowState model
    
⚠️  Services/Workflow/WorkflowTransitionManager.php
    - Hardcoded validation rules
    - Needs to use WorkflowTransition model
    
⚠️  Services/AdvertisementWorkflowService.php
    - Wrapper around WorkflowEngine
    - Will become adapter
```

### Files to Deprecate (Advertisement-Specific)
```
🚫  Config/AdvertisementWorkflow.php
    - Move configuration to database
    - Seeder will populate database
    - Keep file for now, deprecate gradually
    
🚫  DTO/WorkflowActionDTO.php
    - Contains 10 advertisement-specific DTOs
    - Will become adapter-specific
```

### Files to Remove (Later, after migration)
```
🗑️  None in Phase 1
    - Keep all existing files for backward compatibility
    - Remove only after Phase 2 (Approval Engine) completes
```

---

## 📁 New Files to Create

### Database Migrations
```
database/migrations/2026_07_19_000000_create_generic_workflow_tables.php
├── workflow_definitions
├── workflow_states
├── workflow_transitions
├── workflow_instances
├── workflow_instance_steps
└── workflow_idempotency_keys
```

### Models (New)
```
Modules/Core/Models/
├── WorkflowDefinition.php
├── WorkflowState.php
├── WorkflowTransition.php
├── WorkflowInstance.php
├── WorkflowInstanceStep.php
└── WorkflowIdempotencyKey.php
```

### Repositories (New)
```
Modules/Core/Repositories/
├── Contracts/
│   ├── WorkflowDefinitionRepository.php
│   ├── WorkflowInstanceRepository.php
│   ├── WorkflowStateRepository.php
│   ├── WorkflowTransitionRepository.php
│   ├── WorkflowStepRepository.php
│   └── WorkflowIdempotencyRepository.php
└── Eloquent/
    ├── WorkflowDefinitionRepository.php
    ├── WorkflowInstanceRepository.php
    ├── WorkflowStateRepository.php
    ├── WorkflowTransitionRepository.php
    ├── WorkflowStepRepository.php
    └── WorkflowIdempotencyRepository.php
```

### Services (New)
```
Modules/Core/Services/Workflow/
├── WorkflowDefinitionService.php
├── WorkflowInstanceService.php
├── WorkflowTransitionService.php
├── WorkflowAuthorizationService.php
├── WorkflowIdempotencyService.php
├── WorkflowEngine.php (refactored to be generic)
└── WorkflowLockingService.php
```

### Interfaces (New)
```
Modules/Core/Contracts/
├── Workflowable.php
├── WorkflowEntityInterface.php
└── WorkflowRepositoryInterface.php
```

### Events (New - Generic)
```
Modules/Core/Events/
├── WorkflowStarted.php
├── WorkflowTransitioned.php
├── WorkflowCompleted.php
├── WorkflowCancelled.php
└── WorkflowTransitionFailed.php
```

### Backward Compatibility (New - Advertisement Adapter)
```
Modules/Advertisements/Adapters/
├── AdvertisementWorkflowAdapter.php
├── AdvertisementWorkflowServiceAdapter.php
└── AdvertisementControllerAdapter.php

Modules/Advertisements/Seeders/
└── AdvertisementWorkflowDefinitionSeeder.php
```

### Tests (New)
```
Modules/Core/Tests/Feature/
├── WorkflowDefinitionTest.php
├── WorkflowInstanceTest.php
├── WorkflowTransitionTest.php
├── WorkflowLockingTest.php
├── WorkflowIdempotencyTest.php
├── WorkflowAuthorizationTest.php
└── WorkflowPolymorphismTest.php

Modules/Advertisements/Tests/Feature/
├── AdvertisementWorkflowAdapterTest.php
└── AdvertisementWorkflowBackwardCompatibilityTest.php
```

---

## 🗄️ Database Schema Changes

### NEW TABLES

#### workflow_definitions
```sql
CREATE TABLE workflow_definitions (
    id BIGINT PRIMARY KEY,
    uuid VARCHAR(36) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    key VARCHAR(100) NOT NULL,
    description TEXT,
    entity_type VARCHAR(100) NOT NULL,
    version INT DEFAULT 1,
    is_active BOOLEAN DEFAULT TRUE,
    is_default BOOLEAN DEFAULT FALSE,
    configuration JSON,
    created_by BIGINT UNSIGNED,
    updated_by BIGINT UNSIGNED,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    UNIQUE(key, version),
    INDEX(entity_type),
    INDEX(is_active)
);
```

#### workflow_states
```sql
CREATE TABLE workflow_states (
    id BIGINT PRIMARY KEY,
    workflow_definition_id BIGINT NOT NULL,
    name VARCHAR(100) NOT NULL,
    key VARCHAR(100) NOT NULL,
    description TEXT,
    is_initial BOOLEAN DEFAULT FALSE,
    is_final BOOLEAN DEFAULT FALSE,
    is_rejection BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    sort_order INT,
    metadata JSON,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY(workflow_definition_id) REFERENCES workflow_definitions(id),
    UNIQUE(workflow_definition_id, key),
    INDEX(is_initial),
    INDEX(is_final)
);
```

#### workflow_transitions
```sql
CREATE TABLE workflow_transitions (
    id BIGINT PRIMARY KEY,
    workflow_definition_id BIGINT NOT NULL,
    from_state_id BIGINT NOT NULL,
    to_state_id BIGINT NOT NULL,
    name VARCHAR(100),
    key VARCHAR(100) NOT NULL,
    description TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    required_role VARCHAR(100),
    required_permission VARCHAR(100),
    configuration JSON,
    metadata JSON,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY(workflow_definition_id) REFERENCES workflow_definitions(id),
    FOREIGN KEY(from_state_id) REFERENCES workflow_states(id),
    FOREIGN KEY(to_state_id) REFERENCES workflow_states(id),
    UNIQUE(workflow_definition_id, key),
    INDEX(from_state_id, to_state_id)
);
```

#### workflow_instances
```sql
CREATE TABLE workflow_instances (
    id BIGINT PRIMARY KEY,
    uuid VARCHAR(36) UNIQUE NOT NULL,
    workflow_definition_id BIGINT NOT NULL,
    entity_type VARCHAR(100) NOT NULL,
    entity_id BIGINT UNSIGNED NOT NULL,
    current_state_id BIGINT NOT NULL,
    status VARCHAR(50) DEFAULT 'active',
    version INT DEFAULT 1,
    started_at TIMESTAMP,
    completed_at TIMESTAMP NULL,
    cancelled_at TIMESTAMP NULL,
    metadata JSON,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY(workflow_definition_id) REFERENCES workflow_definitions(id),
    FOREIGN KEY(current_state_id) REFERENCES workflow_states(id),
    UNIQUE(entity_type, entity_id, workflow_definition_id),
    INDEX(entity_type, entity_id),
    INDEX(workflow_definition_id),
    INDEX(status),
    INDEX(current_state_id)
);
```

#### workflow_instance_steps
```sql
CREATE TABLE workflow_instance_steps (
    id BIGINT PRIMARY KEY,
    workflow_instance_id BIGINT NOT NULL,
    transition_id BIGINT NOT NULL,
    from_state_id BIGINT NOT NULL,
    to_state_id BIGINT NOT NULL,
    executed_by BIGINT UNSIGNED,
    idempotency_key VARCHAR(255),
    comment TEXT,
    reason TEXT,
    metadata JSON,
    executed_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY(workflow_instance_id) REFERENCES workflow_instances(id),
    FOREIGN KEY(transition_id) REFERENCES workflow_transitions(id),
    FOREIGN KEY(from_state_id) REFERENCES workflow_states(id),
    FOREIGN KEY(to_state_id) REFERENCES workflow_states(id),
    INDEX(workflow_instance_id),
    INDEX(executed_by),
    UNIQUE(workflow_instance_id, idempotency_key)
);
```

#### workflow_idempotency_keys
```sql
CREATE TABLE workflow_idempotency_keys (
    id BIGINT PRIMARY KEY,
    key VARCHAR(255) UNIQUE NOT NULL,
    workflow_instance_id BIGINT NOT NULL,
    transition_id BIGINT,
    request_hash VARCHAR(64),
    executed_by BIGINT UNSIGNED,
    executed_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY(workflow_instance_id) REFERENCES workflow_instances(id),
    UNIQUE(key)
);
```

### MODIFIED TABLES

#### advertisements (add workflow instance reference)
```sql
ALTER TABLE advertisements ADD COLUMN workflow_instance_id BIGINT UNSIGNED NULL;
ALTER TABLE advertisements ADD FOREIGN KEY(workflow_instance_id) REFERENCES workflow_instances(id);
ALTER TABLE advertisements ADD INDEX(workflow_instance_id);
```

Note: Keep `status` field for backward compatibility and fast querying.

---

## 🔄 Migration Strategy

### Step 1: Create New Generic Tables
1. Run migration: `2026_07_19_000000_create_generic_workflow_tables.php`
2. Create all new workflow tables
3. Keep advertisement-specific tables intact

### Step 2: Populate Workflow Definitions
1. Run Advertisement Workflow Definition Seeder
2. Insert `workflow_definitions` row for `advertisement`
3. Insert all states in `workflow_states`
4. Insert all transitions in `workflow_transitions`

### Step 3: Migrate Existing Data (Deferred)
1. For each advertisement with status:
   - Find or create `workflow_instance` for it
   - Map advertisement status to workflow state
   - Update advertisements.workflow_instance_id
2. This step is deferred to Phase 2 to minimize disruption

### Step 4: Backward Compatibility Layer
1. Create AdvertisementWorkflowAdapter
2. Route existing API calls through adapter
3. Adapter calls new generic workflow engine
4. Existing tests continue to pass

---

## 🏗️ Architecture Overview

### Current (Before Phase 1)
```
AdvertisementWorkflowController
    ↓
AdvertisementWorkflowService
    ↓
WorkflowEngine (Advertisement-specific)
    ↓
WorkflowTransitionManager (hardcoded rules)
    ↓
Advertisement.status (enum)
```

### After Phase 1 (Generic + Adapter)
```
AdvertisementWorkflowController
    ↓
AdvertisementWorkflowAdapter ← Backward compatible
    ↓
Generic WorkflowEngine ← NEW (no Advertisement logic)
    ├── WorkflowDefinitionService
    ├── WorkflowInstanceService
    ├── WorkflowTransitionService
    ├── WorkflowAuthorizationService
    ├── WorkflowIdempotencyService
    └── WorkflowLockingService
    ↓
WorkflowDefinition, WorkflowState, WorkflowTransition (database)
    ↓
workflow_instances, workflow_instance_steps (database)
```

### After Phase 2 (With Approval Engine)
```
Same as above + ApprovalEngine + ConditionEngine + ActionHandlers
```

---

## 🔐 Key Design Decisions

### 1. Polymorphic Entity Support
- Use `entity_type` + `entity_id` to link to any business entity
- No hard-coded models in core engine
- Advertisement, KYC, Order, etc. can all use same workflow

### 2. Database-Driven Transitions
- All transitions loaded from `workflow_transitions` table
- No hardcoded match statements
- No hardcoded if/else logic
- Add new transitions via database/admin UI

### 3. Workflow Instances
- Separate from entity state
- One instance per workflow per entity
- Entity can have multiple workflows
- Example: Advertisement + ApprovalWorkflow + DisputeWorkflow

### 4. Transaction Locking
- Use `lockForUpdate()` on WorkflowInstance
- Prevents concurrent transitions
- Ensures consistency

### 5. Idempotency
- Every transition request has optional idempotency_key
- Same key = same result (no duplicate execution)
- Request hash prevents accidental duplicates

### 6. Version Control
- Each WorkflowInstance has version
- Prevent stale updates
- Optimistic locking alternative

### 7. Backward Compatibility
- AdvertisementWorkflowAdapter translates old API to new
- Existing tests still pass
- Gradual migration path

---

## 📋 Files to Modify

### 1. Services/Workflow/WorkflowEngine.php
**Status**: Needs refactoring
**Changes**:
- Remove hardcoded match statement
- Remove all `submitAdvertisement()`, `approveAdvertisement()`, etc. methods
- Replace with single generic `transition()` method
- Load transitions from database, not code
- Support polymorphic entities

### 2. Services/Workflow/WorkflowStateManager.php
**Status**: Needs refactoring
**Changes**:
- Load states from database (WorkflowState model)
- No longer read from config file
- Support multiple workflows

### 3. Services/Workflow/WorkflowTransitionManager.php
**Status**: Needs refactoring
**Changes**:
- Remove hardcoded validation rules
- Load transitions from database
- Load rules from workflow_transitions table
- Support dynamic transitions

### 4. Models/Advertisement.php
**Status**: Minor changes
**Changes**:
- Add `workflow_instance_id` foreign key
- Add relationship: `belongsTo(WorkflowInstance)`
- Implement `Workflowable` interface
- Keep `status` field for backward compatibility

### 5. Providers/AdvertisementWorkflowServiceProvider.php
**Status**: Needs refactoring
**Changes**:
- Register new generic services
- Register repositories
- Register core workflow services
- Keep advertisement-specific bindings for now

---

## 🎯 Implementation Sequence

### Week 1 (Parallel Tasks)

**Task 1: Database & Models**
1. Create migration: `create_generic_workflow_tables.php`
2. Create 6 generic models (WorkflowDefinition, State, Transition, Instance, Step, Idempotency)
3. Create model relationships
4. Test: `php artisan migrate --refresh`

**Task 2: Repositories**
1. Create 6 repository interfaces
2. Create 6 Eloquent repository implementations
3. Test: Verify queries work correctly

**Task 3: Generic Services**
1. Create WorkflowDefinitionService
2. Create WorkflowInstanceService
3. Create WorkflowTransitionService
4. Create WorkflowAuthorizationService
5. Create WorkflowIdempotencyService
6. Create WorkflowLockingService
7. Test: Unit tests for each service

**Task 4: Generic WorkflowEngine**
1. Refactor WorkflowEngine to be generic
2. Remove all Advertisement-specific logic
3. Use database-driven transitions
4. Add transaction locking
5. Add idempotency support
6. Test: Unit and feature tests

**Task 5: Backward Compatibility**
1. Create AdvertisementWorkflowAdapter
2. Update existing Advertisement routes to use adapter
3. Ensure old API still works
4. Test: All existing advertisement workflow tests pass

**Task 6: Advertisement Seeder**
1. Create workflow definition for advertisement
2. Create states, transitions in database
3. Seed workflow configuration
4. Test: Verify database populated correctly

### Week 2 (Testing & Documentation)

**Task 7: Comprehensive Tests**
1. Test generic workflow engine
2. Test polymorphism
3. Test locking
4. Test idempotency
5. Test authorization
6. Test backward compatibility
7. Test edge cases

**Task 8: Documentation**
1. Create implementation summary
2. Update architecture documentation
3. Create migration guide
4. Create Phase 2 requirements

---

## 🚀 Success Criteria

### Core Engine Tests
- [x] WorkflowDefinition can be created
- [x] WorkflowState can be created
- [x] WorkflowTransition can be created
- [x] WorkflowInstance can be created
- [x] Workflow can be started
- [x] Transition can be executed
- [x] Transition is database-driven (not hardcoded)
- [x] Transaction locking prevents concurrent access
- [x] Idempotency key prevents duplicate execution
- [x] Version control prevents stale updates

### Backward Compatibility Tests
- [x] Existing Advertisement workflow tests pass
- [x] Existing API endpoints work
- [x] Advertisement status is in sync with workflow state
- [x] Adapter translates correctly

### Code Quality Tests
- [x] No hardcoded transitions in core engine
- [x] No Advertisement-specific logic in core engine
- [x] All repository methods are implemented
- [x] All services have proper error handling
- [x] Database queries are optimized

### Security Tests
- [x] Authorization is checked
- [x] Permission is checked
- [x] Role is checked
- [x] Policies are enforced

---

## 📦 Deliverables

### Code
1. ✅ Generic workflow models (6 files)
2. ✅ Repository interfaces and implementations (12 files)
3. ✅ Generic services (6 files)
4. ✅ Refactored WorkflowEngine
5. ✅ Backward compatibility adapters (3 files)
6. ✅ Advertisement workflow seeder

### Migrations
1. ✅ Create generic workflow tables

### Tests
1. ✅ Generic workflow tests (7 test files)
2. ✅ Backward compatibility tests (2 test files)

### Documentation
1. ✅ Implementation plan (this file)
2. ✅ Implementation summary
3. ✅ Migration guide
4. ✅ Architecture updated

---

## 🎓 Key Principles Maintained

1. ✅ No duplication (reuse existing working code)
2. ✅ No parallel systems (integrate smoothly)
3. ✅ No breaking changes (backward compatible)
4. ✅ Generic by design (reusable for all entities)
5. ✅ Database-driven (no hardcoding)
6. ✅ Transaction safe (locking, rollback)
7. ✅ Idempotent (duplicate safety)
8. ✅ Authorized (permission checking)
9. ✅ Auditable (comprehensive logging)
10. ✅ Extensible (Phase 2 ready)

---

## ⚠️ Risks & Mitigation

### Risk 1: Database Migration Issues
- **Mitigation**: Test migrations on dev/staging first
- **Mitigation**: Create comprehensive rollback scripts
- **Mitigation**: Document all changes

### Risk 2: Backward Compatibility Breaking
- **Mitigation**: Adapter layer insulates old API
- **Mitigation**: Comprehensive compatibility tests
- **Mitigation**: Gradual migration path

### Risk 3: Performance Regression
- **Mitigation**: Add database indexes
- **Mitigation**: Use query caching where appropriate
- **Mitigation**: Performance tests included

### Risk 4: Concurrency Issues
- **Mitigation**: Database row locking
- **Mitigation**: Concurrency tests
- **Mitigation**: Version control

---

## 📅 Timeline

**Phase 1 Target**: July 19-22, 2026
- Database & Models: July 19
- Repositories: July 19
- Generic Services: July 20
- Generic Engine: July 20
- Adapters: July 21
- Tests: July 21-22
- Documentation: July 22

---

## 🔗 Related Documents

- [WORKFLOW_ARCHITECTURE_AUDIT.md](WORKFLOW_ARCHITECTURE_AUDIT.md) - Current state analysis
- [WORKFLOW_PHASE_1_IMPLEMENTATION_SUMMARY.md](WORKFLOW_PHASE_1_IMPLEMENTATION_SUMMARY.md) - Will be created after implementation

---

**Status**: 📋 Planning Complete
**Next**: Begin Implementation
