<?php

namespace Modules\Advertisements\Tests\Feature;

use App\Models\User;
use App\Models\WorkflowInstance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Modules\Advertisements\Enums\AdvertisementStatus;
use Modules\Advertisements\Models\Advertisement;
use Tests\TestCase;

/**
 * Advertisement Workflow Feature Tests
 *
 * Tests for the complete workflow lifecycle
 */
class AdvertisementWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;
    protected User $operator;
    protected Advertisement $advertisement;

    public function setUp(): void
    {
        parent::setUp();

        Permission::findOrCreate('menu.ads', 'web');

        // Create test users with roles
        $this->owner = User::factory()->create();
        $this->owner->assignRole('user');
        $this->owner->givePermissionTo('menu.ads');

        $this->operator = User::factory()->create();
        $this->operator->assignRole('operator');
        $this->operator->givePermissionTo('menu.ads');

        // Create test advertisement
        $this->advertisement = Advertisement::factory()->create([
            'user_id' => $this->owner->id,
            'status' => AdvertisementStatus::Draft,
        ]);
    }

    /**
     * Test that creating an advertisement also creates a generic workflow instance.
     */
    public function test_creating_advertisement_creates_generic_workflow_instance(): void
    {
        $advertisement = Advertisement::factory()->create([
            'user_id' => $this->owner->id,
            'status' => AdvertisementStatus::Draft,
        ]);

        $instance = WorkflowInstance::where('entity_type', 'Advertisement')
            ->where('entity_id', $advertisement->id)
            ->first();

        $this->assertNotNull($instance);
        $this->assertSame('draft', $instance->currentState?->key);
        $this->assertSame('active', $instance->status);
    }

    /**
     * Test submitting a draft advertisement
     */
    public function test_submit_draft_advertisement(): void
    {
        $response = $this->actingAs($this->owner)
            ->postJson(route('advertisements.workflow.submit', $this->advertisement->uuid), [
                'reason' => 'Ready for submission',
            ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.new_state', 'PendingReview');

        $this->advertisement->refresh();
        $this->assertEquals(AdvertisementStatus::PendingReview, $this->advertisement->status);
    }

    /**
     * Test approving a pending advertisement
     */
    public function test_approve_pending_advertisement(): void
    {
        $this->advertisement->status = AdvertisementStatus::PendingReview;
        $this->advertisement->save();

        $response = $this->actingAs($this->operator)
            ->postJson(route('advertisements.workflow.approve', $this->advertisement->uuid), [
                'reason' => 'All checks passed',
                'comment' => 'Quality listing',
            ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.new_state', 'Approved');

        $this->advertisement->refresh();
        $this->assertEquals(AdvertisementStatus::Approved, $this->advertisement->status);
    }

    /**
     * Test rejecting a pending advertisement
     */
    public function test_reject_pending_advertisement(): void
    {
        $this->advertisement->status = AdvertisementStatus::PendingReview;
        $this->advertisement->save();

        $response = $this->actingAs($this->operator)
            ->postJson(route('advertisements.workflow.reject', $this->advertisement->uuid), [
                'reason' => 'Price too high',
                'description' => 'Market analysis shows this price is not competitive',
            ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.new_state', 'Rejected');

        $this->advertisement->refresh();
        $this->assertEquals(AdvertisementStatus::Rejected, $this->advertisement->status);
    }

    /**
     * Test requesting correction
     */
    public function test_request_correction(): void
    {
        $this->advertisement->status = AdvertisementStatus::PendingReview;
        $this->advertisement->save();

        $response = $this->actingAs($this->operator)
            ->postJson(route('advertisements.workflow.correction', $this->advertisement->uuid), [
                'reason' => 'Missing information',
                'description' => 'Please add more details about the loan terms',
            ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.new_state', 'NeedCorrection');

        $this->advertisement->refresh();
        $this->assertEquals(AdvertisementStatus::NeedCorrection, $this->advertisement->status);
    }

    /**
     * Test publishing an approved advertisement
     */
    public function test_publish_approved_advertisement(): void
    {
        $this->advertisement->status = AdvertisementStatus::Approved;
        $this->advertisement->save();

        $response = $this->actingAs($this->operator)
            ->postJson(route('advertisements.workflow.publish', $this->advertisement->uuid), []);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.new_state', 'Published');

        $this->advertisement->refresh();
        $this->assertEquals(AdvertisementStatus::Published, $this->advertisement->status);
        $this->assertNotNull($this->advertisement->published_at);
    }

    /**
     * Test pausing a published advertisement
     */
    public function test_pause_published_advertisement(): void
    {
        $this->advertisement->status = AdvertisementStatus::Published;
        $this->advertisement->published_at = now();
        $this->advertisement->save();

        $response = $this->actingAs($this->owner)
            ->postJson(route('advertisements.workflow.pause', $this->advertisement->uuid), []);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.new_state', 'Paused');

        $this->advertisement->refresh();
        $this->assertEquals(AdvertisementStatus::Paused, $this->advertisement->status);
    }

    /**
     * Test resuming a paused advertisement
     */
    public function test_resume_paused_advertisement(): void
    {
        $this->advertisement->status = AdvertisementStatus::Paused;
        $this->advertisement->save();

        $response = $this->actingAs($this->owner)
            ->postJson(route('advertisements.workflow.resume', $this->advertisement->uuid), []);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.new_state', 'Published');

        $this->advertisement->refresh();
        $this->assertEquals(AdvertisementStatus::Published, $this->advertisement->status);
    }

    /**
     * Test archiving a published advertisement
     */
    public function test_archive_published_advertisement(): void
    {
        $this->advertisement->status = AdvertisementStatus::Published;
        $this->advertisement->published_at = now();
        $this->advertisement->save();

        $response = $this->actingAs($this->owner)
            ->postJson(route('advertisements.workflow.archive', $this->advertisement->uuid), []);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.new_state', 'Archived');

        $this->advertisement->refresh();
        $this->assertEquals(AdvertisementStatus::Archived, $this->advertisement->status);
    }

    /**
     * Test marking a published advertisement as sold
     */
    public function test_mark_as_sold(): void
    {
        $this->advertisement->status = AdvertisementStatus::Published;
        $this->advertisement->published_at = now();
        $this->advertisement->save();

        $response = $this->actingAs($this->owner)
            ->postJson(route('advertisements.workflow.sold', $this->advertisement->uuid), []);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.new_state', 'Sold');

        $this->advertisement->refresh();
        $this->assertEquals(AdvertisementStatus::Sold, $this->advertisement->status);
    }

    /**
     * Test getting workflow state
     */
    public function test_get_workflow_state(): void
    {
        $this->advertisement->status = AdvertisementStatus::Published;
        $this->advertisement->save();

        $response = $this->actingAs($this->owner)
            ->getJson(route('advertisements.workflow.state', $this->advertisement->uuid));

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.current_state', 'Published');
        $response->assertJsonPath('data.uuid', $this->advertisement->uuid);
    }

    /**
     * Test authorization: owner cannot approve
     */
    public function test_owner_cannot_approve(): void
    {
        $this->advertisement->status = AdvertisementStatus::PendingReview;
        $this->advertisement->save();

        $response = $this->actingAs($this->owner)
            ->postJson(route('advertisements.workflow.approve', $this->advertisement->uuid), [
                'reason' => 'I approve my own ad',
            ]);

        $response->assertStatus(422);
    }

    /**
     * Test authorization: unauthenticated user cannot submit
     */
    public function test_unauthenticated_cannot_submit(): void
    {
        $response = $this->postJson(route('advertisements.workflow.submit', $this->advertisement->uuid), []);

        $response->assertStatus(401);
    }

    /**
     * Test invalid state transition
     */
    public function test_invalid_state_transition(): void
    {
        // Try to approve a draft advertisement (should fail)
        $this->advertisement->status = AdvertisementStatus::Draft;
        $this->advertisement->save();

        $response = $this->actingAs($this->operator)
            ->postJson(route('advertisements.workflow.approve', $this->advertisement->uuid), [
                'reason' => 'Cannot approve draft',
            ]);

        $response->assertStatus(422);
        $response->assertJsonPath('success', false);
    }
}
