<?php

namespace Modules\Advertisements\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use Modules\Advertisements\Models\Advertisement;
use Modules\Advertisements\Models\AdvertisementWorkflowAudit;
use Tests\TestCase;

class DatabaseSchemaReconciliationTest extends TestCase
{
    use RefreshDatabase;

    public function test_advertisement_views_preserve_analytics_when_advertisement_is_deleted(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = ON;');
        }

        $user = User::factory()->create();

        $advertisement = Advertisement::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'seller_user_id' => $user->id,
            'price' => 100000,
            'currency' => 'IRR',
            'title' => 'Schema Reconciliation Test',
            'slug' => 'schema-reconciliation-test',
            'short_description' => 'Short desc',
            'description' => 'A detailed description.',
            'status' => 'Published',
            'visibility' => 'Public',
            'priority' => 0,
            'province_id' => null,
            'city_id' => null,
        ]);

        DB::table('advertisement_views')->insert([
            'advertisement_id' => $advertisement->id,
            'user_id' => null,
            'session_id' => 'schema-reconciliation-session',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $advertisement->forceDelete();

        $view = DB::table('advertisement_views')->where('session_id', 'schema-reconciliation-session')->first();

        $this->assertNotNull($view);
        $this->assertNull($view->advertisement_id);
    }

    public function test_invalid_advertisement_reference_is_rejected_by_foreign_key(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = ON;');
        }

        $this->expectException(\Illuminate\Database\QueryException::class);

        DB::table('advertisement_views')->insert([
            'advertisement_id' => 999999,
            'user_id' => null,
            'session_id' => 'invalid-ad-reference',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_advertisement_workflow_audits_use_advertisement_uuid_as_canonical_reference(): void
    {
        $this->assertTrue(Schema::hasColumn('advertisement_workflow_audits', 'advertisement_uuid'));
        $this->assertFalse(Schema::hasColumn('advertisement_workflow_audits', 'advertisement_id'));

        $user = User::factory()->create();

        $advertisement = Advertisement::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'seller_user_id' => $user->id,
            'price' => 100000,
            'currency' => 'IRR',
            'title' => 'Workflow Audit Canonical Test',
            'slug' => 'workflow-audit-canonical-test',
            'short_description' => 'Short desc',
            'description' => 'A detailed description.',
            'status' => 'Published',
            'visibility' => 'Public',
            'priority' => 0,
            'province_id' => null,
            'city_id' => null,
        ]);

        $audit = AdvertisementWorkflowAudit::create([
            'advertisement_uuid' => $advertisement->uuid,
            'old_state' => 'Draft',
            'new_state' => 'Published',
            'action' => 'publish',
            'user_id' => null,
            'user_role' => 'user',
        ]);

        $this->assertSame($advertisement->uuid, $audit->fresh()->advertisement()->first()?->uuid);
    }
}
