# Current Workflow Architecture

## Status

This document is the canonical source of truth for the current workflow architecture.

## Architecture Overview

The current workflow system is built around a generic, database-driven workflow engine that is not tied to advertisements or any single business domain.

### Active architecture

AdvertisementWorkflowService
    ↓
AdvertisementWorkflowAdapter
    ↓
Generic App\Services\Workflow\WorkflowEngine
    ↓
WorkflowDefinition / WorkflowState / WorkflowTransition
    ↓
WorkflowInstance / WorkflowInstanceStep

## Generic Workflow Engine

The active workflow engine is the generic engine in `app/Services/Workflow/WorkflowEngine.php`.

It is responsible for:

- loading workflow definitions from the database
- creating workflow instances
- validating transitions against definition rules
- executing transitions transactionally
- creating workflow instance steps
- enforcing idempotency and concurrency protections
- dispatching workflow events

### Core concepts

- `WorkflowDefinition`: reusable workflow blueprint
- `WorkflowState`: a state in a workflow definition
- `WorkflowTransition`: a transition between states
- `WorkflowInstance`: a runtime execution of a definition for a specific entity
- `WorkflowInstanceStep`: a single recorded transition step

### Design principles

- generic and domain-agnostic
- database-driven configuration
- no advertisement-specific logic in the engine itself
- polymorphic entity support (for example, advertisement workflows)
- versioned workflow definitions

## Database Structure

The workflow engine persists its state in the database.

### Main tables

- `workflow_definitions`
- `workflow_states`
- `workflow_transitions`
- `workflow_instances`
- `workflow_instance_steps`

### Why this matters

The workflow model is source-driven and runtime-driven from the same database structure, enabling:

- versioning
- reusable definitions
- consistent transition validation
- audit trails
- future extension to KYC, contracts, disputes, and other domain workflows

## Workflow Lifecycle

A workflow normally progresses through this lifecycle:

1. A workflow definition is created or seeded.
2. A workflow instance is started for a specific entity.
3. A transition is requested.
4. The engine validates that the transition is allowed from the current state.
5. The engine executes the transition atomically.
6. A new workflow instance step is recorded.
7. The entity state is synchronized if needed.
8. Events and audit data are emitted.

## Advertisement Integration

Advertisement workflow integration is implemented through the adapter layer.

### AdvertisementWorkflowAdapter

The adapter translates advertisement actions into workflow engine operations.

Responsibilities:

- ensure the advertisement has a workflow instance
- find the correct transition by key
- execute the transition through the generic engine
- sync advertisement status with workflow state
- preserve backward compatibility for advertisement-specific callers

### AdvertisementWorkflowService

The service provides the high-level advertisement workflow API.

Responsibilities:

- expose advertisement workflow methods such as submit, approve, reject, publish, archive, restore, and sold
- coordinate DTOs and response formatting
- delegate to the adapter
- preserve a stable service contract for the advertisement module

## Adapter Pattern

The adapter pattern is the boundary between advertisement-specific concerns and the generic workflow engine.

This keeps the engine:

- generic
- reusable
- free of domain logic

And keeps the advertisement module:
- focused on domain conventions
- compatible with older callers
- able to map advertisement states to workflow states

## Authorization

Authorization is handled at the application boundary.

The workflow engine itself is responsible for execution rules, while controllers, services, and request classes enforce access policy.

## Idempotency

Workflow actions should be idempotent where possible.

The system should avoid duplicate transitions when the same action is requested repeatedly for the same workflow instance.

## Concurrency Protection

Transition execution should be protected from concurrent race conditions.

This is achieved through:

- transactional execution
- locking where applicable
- instance-level transition validation

## Events

Workflow execution can emit events for:

- transition success
- transition failure
- state changes
- audit logging
- downstream integrations

## Audit Trail

Each transition step should be recorded as a workflow instance step.

This provides:

- operational visibility
- replayability
- compliance tracking
- debugging support

## Backward Compatibility

The legacy advertisement-specific workflow engine remains temporarily available for compatibility purposes only.

It is not the active architecture and should not be used for new implementation work.

## Legacy Workflow Deprecation

The legacy `Modules/Advertisements/Services/Workflow/WorkflowEngine.php` remains deprecated.

It is kept only to avoid breaking older callers during migration.

New development should use:

- `App\Services\Workflow\WorkflowEngine`
- `Modules\Advertisements\Adapters\AdvertisementWorkflowAdapter`
- `Modules\Advertisements\Services\AdvertisementWorkflowService`

## Migration Guidance

When extending workflow features:

1. add or update workflow definitions in the database
2. add or update states and transitions through the generic engine model layer
3. implement advertisement-specific mapping in the adapter
4. keep business logic outside the generic engine
5. prefer the adapter/service layer over direct legacy engine usage
