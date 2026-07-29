# Advertisement Workflow Module

This document describes the current advertisement workflow integration with the generic workflow engine.

## Status

This module uses the generic workflow engine as the active workflow architecture.

The current workflow path is:

AdvertisementWorkflowService
    ↓
AdvertisementWorkflowAdapter
    ↓
Generic App\Services\Workflow\WorkflowEngine

## Overview

The advertisement module now integrates with a database-driven, generic workflow engine for workflow state transitions and audit tracking.

## Current vs Legacy Architecture

- Current architecture: AdvertisementWorkflowService → AdvertisementWorkflowAdapter → Generic App\Services\Workflow\WorkflowEngine
- Legacy architecture: deprecated advertisement-specific workflow engine retained only for compatibility

Key capabilities:

- generic workflow definitions and transitions
- advertisement-specific adapter mapping
- workflow instances and steps
- event-driven state transitions
- backward compatibility for older advertisement callers

## Key Features

### ✨ Workflow Engine

- Non-hardcoded, configuration-driven workflow
- Validates all business rules
- Supports dynamic states and transitions
- Handles multi-level approvals

### 🔐 Security

- Role-based access control (RBAC)
- Authorization policies
- Permission matrix by role
- Audit logging every action
- IP and user agent tracking

### 📊 Audit & Compliance

- Complete audit trail
- Every state transition logged
- Approval/rejection tracking
- Compliance-ready implementation

### 🚀 Performance

- Configuration caching
- Query optimization
- Event queueing support
- Async listener processing

### 📱 API-First

- 11 REST API endpoints
- Request validation
- Proper HTTP responses
- Swagger documentation

## Quick Start

### 1. Installation

```bash
# Publish configuration
php artisan vendor:publish --provider="Modules\Advertisements\Providers\AdvertisementWorkflowServiceProvider" --tag="advertisement-workflow-config"

# Run migrations
php artisan migrate

# Seed permissions
php artisan db:seed --class="Modules\Advertisements\Database\Seeders\AdvertisementWorkflowPermissionsSeeder"
```

### 2. Configuration

Edit `config/advertisement-workflow.php`:

```php
return [
    'states' => [
        'Draft' => [...],
        'PendingReview' => [...],
        // ... 11 states
    ],
    
    'transitions' => [
        'Draft' => ['PendingReview'],
        'PendingReview' => ['Approved', 'NeedCorrection', 'Rejected'],
        // ... all transitions
    ],
    
    'role_permissions' => [
        'user' => ['create-advertisement', ...],
        'operator' => ['view-pending-advertisements', ...],
        // ... all roles
    ],
];
```

### 3. Register Service Provider

Add to `config/app.php`:

```php
'providers' => [
    Modules\Advertisements\Providers\AdvertisementWorkflowServiceProvider::class,
],
```

### 4. Test

```bash
php artisan test Modules/Advertisements/Tests/Feature/AdvertisementWorkflowTest.php
```

## API Endpoints

All endpoints require authentication (`auth:sanctum`):

| Method | Endpoint | Action | Required Role |
|--------|----------|--------|---------------|
| POST | `/advertisements/{uuid}/submit` | Submit for review | owner |
| POST | `/advertisements/{uuid}/approve` | Approve | operator+ |
| POST | `/advertisements/{uuid}/reject` | Reject | operator+ |
| POST | `/advertisements/{uuid}/correction` | Request correction | operator+ |
| POST | `/advertisements/{uuid}/publish` | Publish | operator+ |
| POST | `/advertisements/{uuid}/pause` | Pause | owner, operator+ |
| POST | `/advertisements/{uuid}/resume` | Resume | owner, operator+ |
| POST | `/advertisements/{uuid}/archive` | Archive | owner, operator+ |
| POST | `/advertisements/{uuid}/restore` | Restore | senior-operator+ |
| POST | `/advertisements/{uuid}/sold` | Mark as sold | owner |
| GET | `/advertisements/{uuid}/workflow-state` | Get state | authenticated |

## Workflow States

```
┌─────────┐
│  Draft  │ (User creates ad)
└────┬────┘
     │ submit()
┌────▼──────────────┐
│ PendingReview     │ (Operator reviews)
├────┬──────┬───────┤
│    │      │       │
│    │      │   approve()
│    │      │    ↓
│    │      │ ┌─────────┐
│    │      │ │ Approved│
│    │      │ └────┬────┘
│    │      │      │ publish()
│    │      │ ┌────▼──────┐
│    │      │ │ Published │
│    │      │ └──┬─┬─┬────┘
│    │      │    │ │ └─ sold()  ↓ ┌────┐
│    │      │    │ │              │Sold│
│    │      │    │ └─ expired()→ ┌┴────┴┐
│    │      │    │              │Expired│
│    │      │    └─ pause() → Paused ↓
│    │      │
│    │  correction()   ↓ resume() → back to Published
│    │      │
│    │      └─────────→ NeedCorrection
│    │                      │ resubmit (submit())
│    │                      └────→ PendingReview
│    │
│ reject() (or timeout)
│    │
└────▼──────┐
  Rejected   │ (Final state)
     ↓       │
  archive() ─┘
     ↓
┌─────────────┐
│  Archived   │ (Read-only)
└─────────────┘
```

## Roles & Permissions

### User
- Create advertisements
- Update/delete own ads
- Submit for review
- Pause/resume ads
- Archive ads
- View own ads

### Operator
- View pending ads
- Approve advertisements
- Reject advertisements
- Request corrections
- Hide advertisements
- View reports

### Senior Operator
- All operator permissions
- Restore advertisements
- Feature advertisements

### Moderator
- Suspend advertisements
- Remove advertisements
- Investigate reports
- Manage violations

### Admin
- Manage all advertisements
- Force archive
- Force publish
- Force pause
- Change owner
- Manage priorities

### Super Admin
- Manage workflow
- Manage permissions
- Manage roles
- Manage settings
- Manage templates

## Database Schema

### advertisement_workflow_audits
Logs every state transition:

```
- id
- advertisement_id
- uuid
- old_state
- new_state
- user_id
- role
- action
- reason
- comment
- ip_address
- user_agent
- metadata
- created_at
```

### advertisement_logs
Activity log for general tracking:

```
- id
- advertisement_id
- user_id
- action
- ip_address
- user_agent
- metadata
- created_at
```

## Architecture

### Components

1. **WorkflowStateManager** - Manages state properties
2. **WorkflowTransitionManager** - Validates and executes transitions
3. **WorkflowEngine** - Orchestrates all workflow operations
4. **AdvertisementWorkflowService** - High-level service
5. **Policies** - Authorization enforcement
6. **Events** - Workflow state change events
7. **Listeners** - Activity logging, cache, search
8. **Controllers** - REST API endpoints

### Design Patterns

- **Service Layer Pattern** - Business logic in services
- **Repository Pattern** - Data access abstraction
- **DTO Pattern** - Data transfer objects
- **Action Pattern** - Workflow actions
- **Policy Pattern** - Authorization
- **Event Listener Pattern** - Event handling

## Security

- All transitions validated through workflow engine
- Authorization at multiple levels (middleware → policy → service)
- Audit trail for compliance
- IP tracking
- User agent tracking
- Database transactions for consistency

## Testing

Comprehensive feature tests included:

```bash
php artisan test Modules/Advertisements/Tests/Feature/AdvertisementWorkflowTest.php
```

Tests cover:
- State transitions
- Authorization
- Validation
- Event dispatching
- Audit logging
- Error handling

## Configuration

All configuration in `config/advertisement-workflow.php`:

```php
'states' => [...],                    // 11 states
'transitions' => [...],               // Allowed transitions
'role_permissions' => [...],          // Role → permissions
'action_approval' => [...],           // Action → required roles
'edit_rules' => [...],                // Edit restrictions
'delete_rules' => [...],              // Delete rules
'audit' => [...],                     // Audit configuration
'notifications' => [...],             // Notification settings
'events' => [...],                    // Event configuration
'cache' => [...],                     // Cache settings
'features' => [...],                  // Feature flags
```

## Events

11 events dispatched on state changes:

- `AdvertisementSubmitted`
- `AdvertisementApproved`
- `AdvertisementRejected`
- `AdvertisementCorrectionRequested`
- `AdvertisementPublished`
- `AdvertisementPaused`
- `AdvertisementResumed`
- `AdvertisementArchived`
- `AdvertisementRestored`
- `AdvertisementExpired`
- `AdvertisementSold`

## Files Structure

```
Modules/Advertisements/
├── Config/
│   └── AdvertisementWorkflow.php          ← Workflow configuration
├── Services/
│   ├── Workflow/
│   │   ├── WorkflowEngine.php             ← Core engine
│   │   ├── WorkflowStateManager.php       ← State management
│   │   └── WorkflowTransitionManager.php  ← Transition management
│   └── AdvertisementWorkflowService.php   ← High-level service
├── DTO/
│   ├── BaseDTO.php
│   └── WorkflowActionDTO.php              ← All DTOs
├── Events/
│   └── AdvertisementWorkflowEvents.php    ← All 11 events
├── Listeners/
│   └── AdvertisementWorkflowListeners.php ← Event listeners
├── Policies/
│   └── AdvertisementPolicies.php          ← 8 policies
├── Http/
│   ├── Controllers/Api/
│   │   └── AdvertisementWorkflowController.php
│   ├── Requests/
│   │   └── AdvertisementWorkflowRequests.php
│   └── Resources/
│       └── AdvertisementWorkflowResources.php
├── Database/
│   ├── Migrations/
│   │   └── 2024_01_01_000000_create_advertisement_workflow_tables.php
│   └── Seeders/
│       └── AdvertisementWorkflowPermissionsSeeder.php
├── Routes/
│   └── workflow.php
├── Tests/
│   └── Feature/
│       └── AdvertisementWorkflowTest.php
├── WORKFLOW_DOCUMENTATION.md              ← Full documentation
├── IMPLEMENTATION_GUIDE.md                ← Setup guide
└── SWAGGER_DOCUMENTATION.php              ← API docs
```

## Usage Examples

### Submit Advertisement
```php
use Modules\Advertisements\Services\AdvertisementWorkflowService;
use Modules\Advertisements\DTO\SubmitAdvertisementDTO;

$service = app(AdvertisementWorkflowService::class);
$dto = new SubmitAdvertisementDTO();
$response = $service->submit($advertisement, $dto);

if ($response->success) {
    // Success
}
```

### Approve Advertisement
```php
use Modules\Advertisements\DTO\ApproveAdvertisementDTO;

$dto = new ApproveAdvertisementDTO();
$dto->reason = 'All checks passed';
$dto->comment = 'Quality listing';

$response = $service->approve($advertisement, $dto);
```

### Get Workflow State
```php
use Modules\Advertisements\Services\Workflow\WorkflowEngine;

$engine = app(WorkflowEngine::class);
$stateInfo = $engine->getCurrentStateInfo($advertisement);
// Returns: ['state', 'label', 'is_published', 'is_editable', ...]
```

## Documentation

- **WORKFLOW_DOCUMENTATION.md** - Complete workflow documentation
- **IMPLEMENTATION_GUIDE.md** - Step-by-step integration guide
- **SWAGGER_DOCUMENTATION.php** - OpenAPI specification
- **Code Comments** - Inline documentation throughout

## Requirements

- Laravel 12+
- PHP 8.4+
- MySQL/PostgreSQL
- Redis (optional, for caching)
- Spatie Permission package

## Production Deployment

1. Publish configuration
2. Run migrations
3. Seed permissions
4. Register service provider
5. Register policies
6. Configure queue (for async listeners)
7. Set up caching backend
8. Run tests
9. Monitor audit logs

## License

License information would go here.

## Support

For implementation questions, refer to:
- `WORKFLOW_DOCUMENTATION.md` - Architecture & features
- `IMPLEMENTATION_GUIDE.md` - Setup & integration
- `Tests/Feature/` - Usage examples
- `SWAGGER_DOCUMENTATION.php` - API reference

---

**Implementation Date**: July 19, 2026
**Version**: 1.0
**Status**: Production Ready
**Laravel**: 12.0+
**PHP**: 8.4+
