# Advertisement Workflow Module - Quick Reference Guide

## 🚀 Quick Links

| Component | File | Purpose |
|-----------|------|---------|
| **Generic Workflow Engine** | `app/Services/Workflow/WorkflowEngine.php` | Core orchestration |
| **Workflow Definition** | `app/Models/WorkflowDefinition.php` | Workflow blueprint |
| **Workflow State** | `app/Models/WorkflowState.php` | Workflow state definition |
| **Workflow Transition** | `app/Models/WorkflowTransition.php` | Transition definition |
| **Workflow Instance** | `app/Models/WorkflowInstance.php` | Runtime instance |
| **Workflow Instance Step** | `app/Models/WorkflowInstanceStep.php` | Transition history |
| **Advertisement Adapter** | `Modules/Advertisements/Adapters/AdvertisementWorkflowAdapter.php` | Advertisement mapping layer |
| **Workflow Service** | `Modules/Advertisements/Services/AdvertisementWorkflowService.php` | High-level API |
| **DTOs** | `DTO/WorkflowActionDTO.php` | Request/response objects |
| **Events** | `Events/AdvertisementWorkflowEvents.php` | 11 events |
| **Listeners** | `Listeners/AdvertisementWorkflowListeners.php` | Event handlers |
| **Policies** | `Policies/AdvertisementPolicies.php` | Authorization |
| **Controller** | `Http/Controllers/Api/AdvertisementWorkflowController.php` | API endpoints |
| **Requests** | `Http/Requests/AdvertisementWorkflowRequests.php` | Request validation |
| **Resources** | `Http/Resources/AdvertisementWorkflowResources.php` | Response formatting |
| **Routes** | `Routes/workflow.php` | API routes |
| **Migrations** | `Database/migrations/*.php` | Database tables |
| **Seeders** | `Database/Seeders/AdvertisementWorkflowPermissionsSeeder.php` | Permissions setup |
| **Provider** | `Providers/AdvertisementWorkflowServiceProvider.php` | Service registration |
| **Tests** | `Tests/Feature/AdvertisementWorkflowTest.php` | Feature tests |
| **Docs** | `WORKFLOW_README.md` | Quick overview |
| **Docs** | `WORKFLOW_DOCUMENTATION.md` | Complete documentation |
| **Docs** | `IMPLEMENTATION_GUIDE.md` | Setup guide |
| **Docs** | `SWAGGER_DOCUMENTATION.php` | API docs |

---

## 📚 Documentation Roadmap

```
START HERE
    ↓
WORKFLOW_README.md (Overview)
    ↓
Choose your path:
    ├─→ IMPLEMENTATION_GUIDE.md (Setup & Integration)
    │       ├─→ Run migrations
    │       ├─→ Seed permissions
    │       ├─→ Register provider
    │       └─→ Start using
    │
    ├─→ WORKFLOW_DOCUMENTATION.md (Deep dive)
    │       ├─→ Architecture
    │       ├─→ States & Transitions
    │       ├─→ Roles & Permissions
    │       └─→ Audit Logging
    │
    ├─→ SWAGGER_DOCUMENTATION.php (API)
    │       ├─→ All 11 endpoints
    │       ├─→ Request/Response schemas
    │       └─→ Error codes
    │
    └─→ Tests/Feature/AdvertisementWorkflowTest.php (Examples)
            ├─→ How to submit
            ├─→ How to approve
            ├─→ How to reject
            └─→ How to test
```

---

## 🎯 Use Cases

### Use Case: Submit Advertisement for Review
```php
POST /api/advertisements/{uuid}/submit
Authorization: Bearer {token}

{
    "reason": "Ready for submission"
}
```

### Use Case: Approve Advertisement
```php
POST /api/advertisements/{uuid}/approve
Authorization: Bearer {token}

{
    "reason": "All checks passed",
    "comment": "High quality listing"
}
```

### Use Case: Reject Advertisement
```php
POST /api/advertisements/{uuid}/reject
Authorization: Bearer {token}

{
    "reason": "Price too high",
    "description": "Market analysis shows this is not competitive"
}
```

### Use Case: Request Correction
```php
POST /api/advertisements/{uuid}/correction
Authorization: Bearer {token}

{
    "reason": "Missing information",
    "description": "Please add more details about loan terms",
    "fields_to_correct": ["description", "loan_plan"]
}
```

### Use Case: Publish Advertisement
```php
POST /api/advertisements/{uuid}/publish
Authorization: Bearer {token}

{}
```

### Use Case: Pause Advertisement
```php
POST /api/advertisements/{uuid}/pause
Authorization: Bearer {token}

{}
```

### Use Case: Mark as Sold
```php
POST /api/advertisements/{uuid}/sold
Authorization: Bearer {token}

{}
```

---

## 🔑 Key Classes & Methods

### WorkflowEngine

```php
// All transitions through engine
$engine->submitAdvertisement($ad, $payload);
$engine->approveAdvertisement($ad, $payload);
$engine->rejectAdvertisement($ad, $payload);
$engine->requestCorrection($ad, $payload);
$engine->publishAdvertisement($ad, $payload);
$engine->pauseAdvertisement($ad, $payload);
$engine->resumeAdvertisement($ad, $payload);
$engine->archiveAdvertisement($ad, $payload);
$engine->restoreAdvertisement($ad, $payload);
$engine->expireAdvertisement($ad, $payload);
$engine->markAsSold($ad, $payload);

// Get state info
$info = $engine->getCurrentStateInfo($ad);
$available = $engine->getAvailableActions($ad);
```

### WorkflowStateManager

```php
// State queries
$states = $stateManager->getStates();
$state = $stateManager->getState('Published');
$label = $stateManager->getStateLabel('Published');

// State checks
$is_final = $stateManager->isFinalState('Archived');
$is_published = $stateManager->isPublished('Published');
$is_searchable = $stateManager->isSearchable('Published');
$is_editable = $stateManager->isEditable('Draft');

// Transitions
$transitions = $stateManager->getTransitions('Published');
$can = $stateManager->canTransition('Published', 'Paused');
```

### WorkflowTransitionManager

```php
// Execute transition
$success = $transitionManager->transition($ad, $toState, $data);

// Check transition
$can = $transitionManager->canTransition($ad, $toState);
$next_states = $transitionManager->getNextStates($ad);
$history = $transitionManager->getTransitionHistory($ad);
```

### AdvertisementWorkflowService

```php
// High-level operations
$response = $service->submit($ad, $dto);
$response = $service->approve($ad, $dto);
$response = $service->reject($ad, $dto);
$response = $service->requestCorrection($ad, $dto);
$response = $service->publish($ad, $dto);
$response = $service->pause($ad, $dto);
$response = $service->resume($ad, $dto);
$response = $service->archive($ad, $dto);
$response = $service->restore($ad, $dto);
$response = $service->markAsSold($ad, $dto);

// Response structure
$response->success          // bool
$response->message          // string
$response->old_state        // string
$response->new_state        // string
$response->advertisement    // array
```

---

## 📋 Configuration Quick Reference

### Enable/Disable Notifications
```php
'notifications' => [
    'submitted' => true,
    'approved' => true,
    'rejected' => true,
    'correction_requested' => true,
    'published' => true,
    'paused' => true,
    'archived' => true,
],
```

### Configure Caching
```php
'cache' => [
    'enabled' => true,
    'ttl' => 3600,
    'prefix' => 'advertisement_workflow',
],
```

### Toggle Features
```php
'features' => [
    'correction_requests' => true,
    'rejection_reasons' => true,
    'approval_comments' => true,
    'workflow_templates' => true,
    'bulk_actions' => false,
    'workflow_analytics' => true,
],
```

---

## 🧪 Testing Commands

```bash
# Run all workflow tests
php artisan test Modules/Advertisements/Tests/Feature/AdvertisementWorkflowTest.php

# Run specific test
php artisan test --filter=test_submit_draft_advertisement

# Run with coverage
php artisan test --coverage Modules/Advertisements/Tests/

# Run and stop on first failure
php artisan test --stop-on-failure Modules/Advertisements/Tests/
```

---

## 🔍 Debugging Tips

### Check Current State
```php
$ad = Advertisement::find($id);
echo $ad->status->value;  // 'Published'
```

### Check Audit History
```php
$audits = $ad->auditLog()->latest()->get();
foreach ($audits as $audit) {
    echo "{$audit->old_state} → {$audit->new_state} by {$audit->user->name}";
}
```

### Check Permissions
```php
$user = auth()->user();
$user->hasRole('operator');
$user->hasPermission('approve-advertisement');
$user->hasAnyRole(['operator', 'admin']);
```

### Debug Transitions
```php
$engine = app(WorkflowEngine::class);
$next_states = $engine->getAvailableActions($ad);
echo json_encode($next_states, JSON_PRETTY_PRINT);
```

---

## 💾 Database Queries

### Get All Pending Advertisements
```php
use Modules\Advertisements\Enums\AdvertisementStatus;

$pending = Advertisement::where('status', AdvertisementStatus::PendingReview)->get();
```

### Get Audit Trail
```php
$audits = AdvertisementWorkflowAudit::where('advertisement_id', $id)
    ->orderBy('created_at', 'desc')
    ->get();
```

### Get Advertisement Activity
```php
$activity = AdvertisementLog::where('advertisement_id', $id)
    ->orderBy('created_at', 'desc')
    ->get();
```

### Get Approvals by User
```php
$approvals = AdvertisementWorkflowAudit::where('action', 'approve')
    ->where('user_id', auth()->id())
    ->get();
```

### Get Rejections
```php
$rejections = AdvertisementWorkflowAudit::where('action', 'reject')
    ->orderBy('created_at', 'desc')
    ->get();
```

---

## 🛠️ Common Tasks

### Create Advertisement & Submit
```php
$ad = Advertisement::create([
    'title' => 'BMW X5',
    'price' => 50000,
    'user_id' => auth()->id(),
    'status' => AdvertisementStatus::Draft,
]);

$service = app(AdvertisementWorkflowService::class);
$dto = new SubmitAdvertisementDTO();
$response = $service->submit($ad, $dto);
```

### Approve Multiple Advertisements
```php
$pending = Advertisement::where('status', AdvertisementStatus::PendingReview)->get();
$service = app(AdvertisementWorkflowService::class);

foreach ($pending as $ad) {
    $dto = new ApproveAdvertisementDTO();
    $dto->reason = 'Bulk approval';
    $service->approve($ad, $dto);
}
```

### Export Audit Trail
```php
$audits = AdvertisementWorkflowAudit::where('advertisement_id', $id)
    ->orderBy('created_at', 'desc')
    ->get();

return $audits->map(fn($a) => [
    'timestamp' => $a->created_at,
    'user' => $a->user->name,
    'action' => $a->action,
    'old_state' => $a->old_state,
    'new_state' => $a->new_state,
    'reason' => $a->reason,
]);
```

---

## 📞 Getting Help

1. **Can't find config?** → See `Config/AdvertisementWorkflow.php`
2. **How to use API?** → See `SWAGGER_DOCUMENTATION.php`
3. **Integration issues?** → See `IMPLEMENTATION_GUIDE.md`
4. **Architecture questions?** → See `WORKFLOW_DOCUMENTATION.md`
5. **Code examples?** → See `Tests/Feature/AdvertisementWorkflowTest.php`
6. **Quick overview?** → See `WORKFLOW_README.md`

---

## ✅ Checklist for New Developer

- [ ] Read `WORKFLOW_README.md`
- [ ] Review `WORKFLOW_DOCUMENTATION.md`
- [ ] Follow `IMPLEMENTATION_GUIDE.md`
- [ ] Run migrations: `php artisan migrate`
- [ ] Seed permissions: `php artisan db:seed --class=AdvertisementWorkflowPermissionsSeeder`
- [ ] Run tests: `php artisan test`
- [ ] Review `SWAGGER_DOCUMENTATION.php`
- [ ] Test API endpoints
- [ ] Check audit logs
- [ ] Review `Tests/Feature/AdvertisementWorkflowTest.php`

---

## 🎯 Success Criteria

✅ All 11 API endpoints working
✅ All state transitions validating
✅ All roles and permissions configured
✅ Audit trail logging all transitions
✅ Events dispatching for all state changes
✅ Tests passing (15+ tests)
✅ Authorization enforced at all levels
✅ API documentation complete
✅ Implementation guide available
✅ Production ready

---

**Quick Navigation**:
- 📖 [Overview](WORKFLOW_README.md)
- 📚 [Full Documentation](WORKFLOW_DOCUMENTATION.md)
- 🔧 [Setup Guide](IMPLEMENTATION_GUIDE.md)
- 🔌 [API Reference](SWAGGER_DOCUMENTATION.php)
- 📋 [Summary](IMPLEMENTATION_SUMMARY.md)
- 🧪 [Tests](Tests/Feature/AdvertisementWorkflowTest.php)
- ⚙️ [Config](Config/AdvertisementWorkflow.php)

---

**Last Updated**: July 19, 2026
**Version**: 1.0
**Status**: ✅ Production Ready
