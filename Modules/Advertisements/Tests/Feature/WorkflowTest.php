<?php

namespace Modules\Advertisements\Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Modules\Advertisements\Models\AdvertisementWorkflowAudit;

class WorkflowTest extends TestCase
{
    public function test_user_can_submit_advertisement_and_audit_is_created(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');

        $adModel = \Modules\Advertisements\Models\Advertisement::create([
            'uuid' => \Illuminate\Support\Str::uuid()->toString(),
            'advertisement_number' => 'AD-' . rand(1000, 9999),
            'user_id' => $user->id,
            'title' => 'Test Ad',
            'slug' => 'test-ad-' . rand(1000, 9999),
            'description' => 'Test description',
            'status' => 'Draft',
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/advertisements/' . $adModel->uuid . '/submit');

        dump($response->getContent());

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonPath('data.new_state', 'PendingReview');

        $this->assertDatabaseHas('advertisement_workflow_audits', [
            'advertisement_uuid' => $adModel->uuid,
            'action' => 'submit',
            'new_state' => 'PendingReview',
        ]);
    }
}
