# Advertisement Workflow Module - Implementation Guide

## Quick Start

This guide explains how the current advertisement workflow integrates with the generic workflow engine.

## Current Implementation Status

✅ **Completed:**
- Generic workflow engine integration
- Workflow definitions and transitions persisted in the database
- Workflow instance and step tracking
- Advertisement adapter mapping
- Advertisement workflow service integration
- Events and listeners
- API routes for workflow actions
- Current documentation and architecture notes

⚠️ **Important:** the legacy advertisement-specific workflow engine is deprecated and remains only for compatibility. New work should flow through the generic workflow engine and the advertisement adapter/service layer.

## Step-by-Step Integration

### 1. Publish Configuration

```bash
php artisan vendor:publish --provider="Modules\Advertisements\Providers\AdvertisementWorkflowServiceProvider" --tag="advertisement-workflow-config"
```

### 2. Register Service Provider

Add to `config/app.php`:
```php
'providers' => [
    // ...
    Modules\Advertisements\Providers\AdvertisementWorkflowServiceProvider::class,
],
```

Or register in module's service provider.

### 3. Run Migrations

```bash
php artisan migrate
```

This creates:
- `advertisement_logs` - Activity log table
- `advertisement_workflow_audits` - Workflow audit table
- `advertisement_workflow_states` - State configuration table
- `advertisement_workflow_transitions` - Transition configuration table

### 4. Seed Permissions and Roles

```bash
php artisan db:seed --class="Modules\Advertisements\Database\Seeders\AdvertisementWorkflowPermissionsSeeder"
```

This creates:
- All 6 roles (user, operator, senior-operator, moderator, admin, super-admin)
- All permissions for each role
- Role-permission assignments

### 5. Register Policies

In `app/Providers/AuthServiceProvider.php`:

```php
use Modules\Advertisements\Models\Advertisement;
use Modules\Advertisements\Policies\ViewAdvertisementPolicy;
use Modules\Advertisements\Policies\CreateAdvertisementPolicy;
use Modules\Advertisements\Policies\UpdateAdvertisementPolicy;
use Modules\Advertisements\Policies\DeleteAdvertisementPolicy;
use Modules\Advertisements\Policies\ApproveAdvertisementPolicy;
use Modules\Advertisements\Policies\RejectAdvertisementPolicy;
use Modules\Advertisements\Policies\PublishAdvertisementPolicy;
use Modules\Advertisements\Policies\ArchiveAdvertisementPolicy;
use Modules\Advertisements\Policies\RestoreAdvertisementPolicy;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Advertisement::class => AdvertisementPolicies::class,
    ];
}
```

### 6. Register Event Listeners (Optional)

The service provider handles this automatically, but you can customize in `config/advertisement-workflow.php`.

### 7. Update Advertisement Model

Add relationships to the Advertisement model:

```php
public function logs()
{
    return $this->hasMany(AdvertisementLog::class);
}

public function auditLog()
{
    return $this->hasMany(AdvertisementWorkflowAudit::class, 'advertisement_id');
}
```

### 8. Test the Implementation

```bash
php artisan test Modules/Advertisements/Tests/Feature/AdvertisementWorkflowTest.php
```

## API Usage Examples

### Submit Advertisement

```bash
POST /api/advertisements/550e8400-e29b-41d4-a716-446655440000/submit
Authorization: Bearer {token}
Content-Type: application/json

{
    "reason": "Ready for submission"
}
```

Response:
```json
{
    "success": true,
    "message": "Advertisement submitted successfully",
    "data": {
        "old_state": "Draft",
        "new_state": "PendingReview",
        "advertisement": {
            "id": 1,
            "uuid": "550e8400-e29b-41d4-a716-446655440000",
            "title": "BMW X5",
            "status": "PendingReview",
            "created_at": "2024-01-01T12:00:00Z",
            "updated_at": "2024-01-01T12:00:01Z"
        }
    }
}
```

### Approve Advertisement

```bash
POST /api/advertisements/{uuid}/approve
Authorization: Bearer {token}
Content-Type: application/json

{
    "reason": "All checks passed",
    "comment": "High quality listing"
}
```

### Get Workflow State

```bash
GET /api/advertisements/{uuid}/workflow-state
Authorization: Bearer {token}
```

## Configuration

Edit `config/advertisement-workflow.php` to:

1. **Customize States**
   ```php
   'states' => [
       'CustomState' => [
           'label' => 'Custom State',
           'description' => 'Description',
           'is_final' => false,
           'is_published' => false,
           // ... properties
       ]
   ]
   ```

2. **Modify Transitions**
   ```php
   'transitions' => [
       'Draft' => ['CustomState'],
       'CustomState' => ['PendingReview'],
   ]
   ```

3. **Adjust Role Permissions**
   ```php
   'role_permissions' => [
       'custom-role' => [
           'permission-1',
           'permission-2',
       ]
   ]
   ```

4. **Configure Features**
   ```php
   'features' => [
       'correction_requests' => true,
       'rejection_reasons' => true,
       'approval_comments' => true,
   ]
   ```

## Database Queries

### Get Advertisement Audit History

```php
use Modules\Advertisements\Models\AdvertisementWorkflowAudit;

$audits = AdvertisementWorkflowAudit::where('advertisement_id', $id)
    ->orderBy('created_at', 'desc')
    ->get();
```

### Get Activity Logs

```php
use Modules\Advertisements\Models\AdvertisementLog;

$logs = AdvertisementLog::where('advertisement_id', $id)
    ->orderBy('created_at', 'desc')
    ->get();
```

### Find All Pending Advertisements

```php
use Modules\Advertisements\Models\Advertisement;
use Modules\Advertisements\Enums\AdvertisementStatus;

$pending = Advertisement::where('status', AdvertisementStatus::PendingReview)
    ->get();
```

## Service Usage

### Inject Service

```php
use Modules\Advertisements\Services\AdvertisementWorkflowService;

public function approveAdvertisement(
    Advertisement $advertisement,
    AdvertisementWorkflowService $workflowService
) {
    $dto = new ApproveAdvertisementDTO();
    $dto->reason = 'All requirements met';
    
    $response = $workflowService->approve($advertisement, $dto);
    
    if ($response->success) {
        // Handle success
    }
}
```

## Event Handling

### Listen to Events

```php
use Modules\Advertisements\Events\AdvertisementPublished;
use Illuminate\Events\Dispatcher;

public function boot(Dispatcher $events)
{
    $events->listen(AdvertisementPublished::class, function (AdvertisementPublished $event) {
        // Custom handling
        logger()->info('Ad published: ' . $event->advertisement->uuid);
    });
}
```

## Error Handling

All workflow operations return a response object:

```php
$response = $workflowService->submit($advertisement, $dto);

if (!$response->success) {
    // $response->message contains error message
    return response()->json([
        'error' => $response->message
    ], 422);
}

// $response->old_state
// $response->new_state
// $response->advertisement
```

## Caching

Clear workflow cache:

```bash
php artisan cache:forget advertisement_workflow_states
php artisan cache:forget advertisement_workflow_transitions
```

Or programmatically:

```php
use Illuminate\Support\Facades\Cache;

Cache::forget('advertisement_workflow:states');
Cache::forget('advertisement_workflow:transitions');
```

## Audit Trail

Every state transition is logged to `advertisement_workflow_audits`:

```php
$auditEntry = AdvertisementWorkflowAudit::find($id);

echo $auditEntry->old_state;      // 'Draft'
echo $auditEntry->new_state;      // 'PendingReview'
echo $auditEntry->action;         // 'submit'
echo $auditEntry->ip_address;     // '192.168.1.1'
echo $auditEntry->user_agent;     // 'Mozilla...'
```

## Notifications

To send notifications on workflow events, create listeners:

```php
namespace Modules\Advertisements\Listeners;

use Modules\Advertisements\Events\AdvertisementPublished;
use Modules\Advertisements\Notifications\AdvertisementPublishedNotification;

class SendAdvertisementPublishedNotification
{
    public function handle(AdvertisementPublished $event)
    {
        $event->advertisement->user->notify(
            new AdvertisementPublishedNotification($event->advertisement)
        );
    }
}
```

## Search Integration

To integrate with Elasticsearch, Meilisearch, or Algolia:

```php
namespace Modules\Advertisements\Listeners;

use Modules\Advertisements\Events\AdvertisementPublished;

class IndexInSearch
{
    public function handle(AdvertisementPublished $event)
    {
        // Index to Elasticsearch
        // Index to Meilisearch
        // Index to Algolia
    }
}
```

## Testing

### Unit Tests

```php
use App\Services\Workflow\WorkflowEngine;

public function test_generic_workflow_transition()
{
    $engine = app(WorkflowEngine::class);

    $this->assertNotNull($engine);
}
```

### Feature Tests

```bash
php artisan test Modules/Advertisements/Tests/Feature/AdvertisementWorkflowTest.php
```

## Troubleshooting

### 401 Unauthorized
- User not authenticated with Sanctum token
- Check `Authorization: Bearer {token}` header

### 403 Forbidden
- User lacks required role/permission
- Check `config/advertisement-workflow.php` for role requirements
- Verify role assignment: `$user->assignRole('operator')`

### 422 Unprocessable Entity
- Invalid state transition
- Check current state matches transition requirements
- Verify user has required permissions

### No events being triggered
- Check event listeners are registered in service provider
- Verify queue is configured if using async listeners
- Enable `'events'` in `config/advertisement-workflow.php`

## Performance Optimization

1. **Cache Configuration**
   ```php
   'cache' => [
       'enabled' => true,
       'ttl' => 3600,
       'prefix' => 'advertisement_workflow',
   ]
   ```

2. **Queue Listeners**
   ```php
   class LogAdvertisementActivity implements ShouldQueue
   {
       use InteractsWithQueue;
       
       public $delay = 5;  // Delay 5 seconds
   }
   ```

3. **Index Audit Logs**
   ```sql
   CREATE INDEX idx_audit_advertisement ON advertisement_workflow_audits(advertisement_id);
   CREATE INDEX idx_audit_created ON advertisement_workflow_audits(created_at);
   ```

## Production Checklist

- [ ] Run migrations
- [ ] Seed permissions and roles
- [ ] Register service provider
- [ ] Register policies
- [ ] Configure queue for listeners
- [ ] Set up logging for audit trail
- [ ] Configure cache backend (Redis)
- [ ] Run tests: `php artisan test`
- [ ] Test all workflow transitions
- [ ] Test authorization for each role
- [ ] Set up monitoring/alerts
- [ ] Document custom configuration
- [ ] Train operators on workflow
- [ ] Set up backup for audit logs

## Support & Documentation

- **Configuration**: `Modules/Advertisements/Config/AdvertisementWorkflow.php`
- **Workflow Engine**: `app/Services/Workflow/WorkflowEngine.php`
- **API**: `Modules/Advertisements/Http/Controllers/Api/AdvertisementWorkflowController.php`
- **Events**: `Modules/Advertisements/Events/`
- **Tests**: `Modules/Advertisements/Tests/`
- **Documentation**: `Modules/Advertisements/WORKFLOW_DOCUMENTATION.md`
- **Swagger**: `Modules/Advertisements/SWAGGER_DOCUMENTATION.php`

## Support

For issues:
1. Check configuration in `config/advertisement-workflow.php`
2. Review workflow engine logs
3. Check audit trail in database
4. Review tests for usage examples
5. Check API response for error messages

---

**Version**: 1.0
**Last Updated**: 2026-07-19
**Laravel Version**: 12.0+
**PHP Version**: 8.4+
