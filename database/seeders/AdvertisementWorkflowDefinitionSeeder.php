<?php

namespace Database\Seeders;

use App\Models\WorkflowDefinition;
use App\Models\WorkflowState;
use App\Models\WorkflowTransition;
use Illuminate\Database\Seeder;

/**
 * AdvertisementWorkflowDefinitionSeeder
 * 
 * Seeds the generic workflow tables with the Advertisement workflow definition.
 * This creates:
 * - 1 WorkflowDefinition row
 * - 11 WorkflowState rows
 * - 12 WorkflowTransition rows
 * 
 * All the workflow logic is now in the database, not hardcoded in PHP.
 */
class AdvertisementWorkflowDefinitionSeeder extends Seeder
{
    public function run(): void
    {
        // Create the workflow definition
        $definition = WorkflowDefinition::firstOrCreate(
            ['key' => 'advertisement.approval', 'version' => 1],
            [
                'name' => 'Advertisement Approval',
                'description' => 'Workflow for reviewing and publishing advertisements',
                'entity_type' => 'Advertisement',
                'is_active' => true,
                'is_default' => true,
                'created_by' => 1, // System user
            ]
        );

        // Create workflow states
        $states = $this->createStates($definition);

        // Create workflow transitions
        $this->createTransitions($definition, $states);
    }

    protected function createStates(WorkflowDefinition $definition): array
    {
        $statesData = [
            [
                'name' => 'Draft',
                'key' => 'draft',
                'description' => 'Advertisement is in draft status',
                'is_initial' => true,
                'is_final' => false,
                'is_rejection' => false,
                'sort_order' => 1,
            ],
            [
                'name' => 'Pending Review',
                'key' => 'pending_review',
                'description' => 'Advertisement is waiting for review',
                'is_initial' => false,
                'is_final' => false,
                'is_rejection' => false,
                'sort_order' => 2,
            ],
            [
                'name' => 'Needs Correction',
                'key' => 'needs_correction',
                'description' => 'Advertisement requires corrections',
                'is_initial' => false,
                'is_final' => false,
                'is_rejection' => false,
                'sort_order' => 3,
            ],
            [
                'name' => 'Rejected',
                'key' => 'rejected',
                'description' => 'Advertisement was rejected',
                'is_initial' => false,
                'is_final' => true,
                'is_rejection' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Approved',
                'key' => 'approved',
                'description' => 'Advertisement is approved',
                'is_initial' => false,
                'is_final' => false,
                'is_rejection' => false,
                'sort_order' => 5,
            ],
            [
                'name' => 'Published',
                'key' => 'published',
                'description' => 'Advertisement is published and visible',
                'is_initial' => false,
                'is_final' => false,
                'is_rejection' => false,
                'sort_order' => 6,
            ],
            [
                'name' => 'Paused',
                'key' => 'paused',
                'description' => 'Advertisement is paused',
                'is_initial' => false,
                'is_final' => false,
                'is_rejection' => false,
                'sort_order' => 7,
            ],
            [
                'name' => 'Expired',
                'key' => 'expired',
                'description' => 'Advertisement has expired',
                'is_initial' => false,
                'is_final' => true,
                'is_rejection' => false,
                'sort_order' => 8,
            ],
            [
                'name' => 'Sold',
                'key' => 'sold',
                'description' => 'Advertisement item is sold',
                'is_initial' => false,
                'is_final' => true,
                'is_rejection' => false,
                'sort_order' => 9,
            ],
            [
                'name' => 'Archived',
                'key' => 'archived',
                'description' => 'Advertisement is archived and read-only',
                'is_initial' => false,
                'is_final' => true,
                'is_rejection' => false,
                'sort_order' => 10,
            ],
            [
                'name' => 'Deleted',
                'key' => 'deleted',
                'description' => 'Advertisement is deleted',
                'is_initial' => false,
                'is_final' => true,
                'is_rejection' => false,
                'sort_order' => 11,
            ],
        ];

        $states = [];
        foreach ($statesData as $data) {
            $state = WorkflowState::firstOrCreate(
                ['workflow_definition_id' => $definition->id, 'key' => $data['key']],
                array_merge($data, ['workflow_definition_id' => $definition->id])
            );
            $states[$data['key']] = $state;
        }

        return $states;
    }

    protected function createTransitions(WorkflowDefinition $definition, array $states): void
    {
        $transitions = [
            [
                'from' => 'draft',
                'to' => 'pending_review',
                'key' => 'submit',
                'name' => 'Submit',
                'required_role' => 'user,owner',
            ],
            [
                'from' => 'pending_review',
                'to' => 'approved',
                'key' => 'approve',
                'name' => 'Approve',
                'required_role' => 'operator,senior-operator,admin',
            ],
            [
                'from' => 'pending_review',
                'to' => 'needs_correction',
                'key' => 'request_correction',
                'name' => 'Request Correction',
                'required_role' => 'operator,senior-operator,admin',
            ],
            [
                'from' => 'pending_review',
                'to' => 'rejected',
                'key' => 'reject',
                'name' => 'Reject',
                'required_role' => 'operator,senior-operator,admin',
            ],
            [
                'from' => 'needs_correction',
                'to' => 'pending_review',
                'key' => 'resubmit',
                'name' => 'Resubmit',
                'required_role' => 'user,owner',
            ],
            [
                'from' => 'approved',
                'to' => 'published',
                'key' => 'publish',
                'name' => 'Publish',
                'required_role' => 'operator,senior-operator,admin',
            ],
            [
                'from' => 'published',
                'to' => 'paused',
                'key' => 'pause',
                'name' => 'Pause',
                'required_role' => 'user,owner,operator,senior-operator,admin',
            ],
            [
                'from' => 'paused',
                'to' => 'published',
                'key' => 'resume',
                'name' => 'Resume',
                'required_role' => 'user,owner,operator,senior-operator,admin',
            ],
            [
                'from' => 'published',
                'to' => 'sold',
                'key' => 'mark_sold',
                'name' => 'Mark as Sold',
                'required_role' => 'user,owner,admin',
            ],
            [
                'from' => 'published',
                'to' => 'expired',
                'key' => 'expire',
                'name' => 'Expire',
                'required_role' => 'system,admin',
            ],
            [
                'from' => 'published',
                'to' => 'archived',
                'key' => 'archive_from_published',
                'name' => 'Archive From Published',
                'required_role' => 'user,owner,operator,senior-operator,admin',
            ],
            [
                'from' => 'rejected',
                'to' => 'archived',
                'key' => 'archive_from_rejected',
                'name' => 'Archive From Rejected',
                'required_role' => 'user,owner,operator,senior-operator,admin',
            ],
            [
                'from' => 'archived',
                'to' => 'draft',
                'key' => 'restore',
                'name' => 'Restore',
                'required_role' => 'senior-operator,admin',
            ],
        ];

        foreach ($transitions as $transitionData) {
            $fromState = $states[$transitionData['from']];
            $toState = $states[$transitionData['to']];

            WorkflowTransition::firstOrCreate(
                ['workflow_definition_id' => $definition->id, 'key' => $transitionData['key']],
                [
                    'workflow_definition_id' => $definition->id,
                    'from_state_id' => $fromState->id,
                    'to_state_id' => $toState->id,
                    'key' => $transitionData['key'],
                    'name' => $transitionData['name'],
                    'required_role' => $transitionData['required_role'],
                    'is_active' => true,
                ]
            );
        }
    }
}
