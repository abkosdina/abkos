<?php

namespace Modules\Deals\Database\Seeders;

use App\Models\WorkflowDefinition;
use App\Models\WorkflowState;
use App\Models\WorkflowTransition;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;

class DealWorkflowDefinitionSeeder extends Seeder
{
    public function run(): void
    {
        $definition = WorkflowDefinition::firstOrCreate(
            ['key' => 'deal.lifecycle', 'version' => 1],
            [
                'uuid' => Str::uuid()->toString(),
                'name' => 'Deal Lifecycle',
                'slug' => 'deal-lifecycle',
                'description' => 'Workflow for deal lifecycle management',
                'entity_type' => 'Deal',
                'is_active' => true,
                'is_default' => true,
                'created_by' => null,
            ]
        );

        $states = $this->createStates($definition);
        $this->createTransitions($definition, $states);
    }

    protected function createStates(WorkflowDefinition $definition): array
    {
        $statesData = [
            [
                'name' => 'Pending',
                'key' => 'pending',
                'description' => 'Deal has been created and is pending buyer/seller confirmation.',
                'is_initial' => true,
                'is_final' => false,
                'is_rejection' => false,
                'sort_order' => 1,
            ],
            [
                'name' => 'Awaiting Payment',
                'key' => 'awaiting_payment',
                'description' => 'Deal is waiting for payment to be initiated.',
                'is_initial' => false,
                'is_final' => false,
                'is_rejection' => false,
                'sort_order' => 2,
            ],
            [
                'name' => 'Payment Processing',
                'key' => 'payment_processing',
                'description' => 'Deal payment is currently being processed.',
                'is_initial' => false,
                'is_final' => false,
                'is_rejection' => false,
                'sort_order' => 3,
            ],
            [
                'name' => 'Payment Completed',
                'key' => 'payment_completed',
                'description' => 'Deal payment was completed successfully.',
                'is_initial' => false,
                'is_final' => false,
                'is_rejection' => false,
                'sort_order' => 4,
            ],
            [
                'name' => 'Escrow Active',
                'key' => 'escrow_active',
                'description' => 'Funds are held in escrow awaiting seller fulfillment.',
                'is_initial' => false,
                'is_final' => false,
                'is_rejection' => false,
                'sort_order' => 5,
            ],
            [
                'name' => 'Seller Processing',
                'key' => 'seller_processing',
                'description' => 'Seller is processing the delivery of goods or services.',
                'is_initial' => false,
                'is_final' => false,
                'is_rejection' => false,
                'sort_order' => 6,
            ],
            [
                'name' => 'Completed',
                'key' => 'completed',
                'description' => 'Deal is completed successfully.',
                'is_initial' => false,
                'is_final' => true,
                'is_rejection' => false,
                'sort_order' => 7,
            ],
            [
                'name' => 'Cancelled',
                'key' => 'cancelled',
                'description' => 'Deal has been cancelled.',
                'is_initial' => false,
                'is_final' => true,
                'is_rejection' => true,
                'sort_order' => 8,
            ],
            [
                'name' => 'Expired',
                'key' => 'expired',
                'description' => 'Deal expired due to inactivity or failure to pay.',
                'is_initial' => false,
                'is_final' => true,
                'is_rejection' => true,
                'sort_order' => 9,
            ],
            [
                'name' => 'Disputed',
                'key' => 'disputed',
                'description' => 'Deal is disputed and requires review.',
                'is_initial' => false,
                'is_final' => false,
                'is_rejection' => false,
                'sort_order' => 10,
            ],
            [
                'name' => 'Refunded',
                'key' => 'refunded',
                'description' => 'Deal has been refunded.',
                'is_initial' => false,
                'is_final' => true,
                'is_rejection' => true,
                'sort_order' => 11,
            ],
        ];

        $states = [];
        foreach ($statesData as $data) {
            $state = WorkflowState::firstOrCreate(
                ['workflow_definition_id' => $definition->id, 'key' => $data['key']],
                array_merge($data, ['workflow_definition_id' => $definition->id, 'uuid' => Str::uuid()->toString()])
            );
            $states[$data['key']] = $state;
        }

        return $states;
    }

    protected function createTransitions(WorkflowDefinition $definition, array $states): void
    {
        $transitions = [
            [
                'from' => 'pending',
                'to' => 'awaiting_payment',
                'key' => 'request_payment',
                'name' => 'Request Payment',
                'required_role' => '',
            ],
            [
                'from' => 'awaiting_payment',
                'to' => 'payment_processing',
                'key' => 'start_payment',
                'name' => 'Start Payment Processing',
                'required_role' => '',
            ],
            [
                'from' => 'payment_processing',
                'to' => 'payment_completed',
                'key' => 'complete_payment',
                'name' => 'Complete Payment',
                'required_role' => '',
            ],
            [
                'from' => 'payment_completed',
                'to' => 'escrow_active',
                'key' => 'activate_escrow',
                'name' => 'Activate Escrow',
                'required_role' => '',
            ],
            [
                'from' => 'escrow_active',
                'to' => 'seller_processing',
                'key' => 'start_seller_processing',
                'name' => 'Start Seller Processing',
                'required_role' => '',
            ],
            [
                'from' => 'seller_processing',
                'to' => 'completed',
                'key' => 'complete',
                'name' => 'Complete Deal',
                'required_role' => '',
            ],
            [
                'from' => 'pending',
                'to' => 'cancelled',
                'key' => 'cancel',
                'name' => 'Cancel Deal',
                'required_role' => '',
            ],
            [
                'from' => 'awaiting_payment',
                'to' => 'expired',
                'key' => 'expire',
                'name' => 'Expire Deal',
                'required_role' => '',
            ],
            [
                'from' => 'payment_processing',
                'to' => 'disputed',
                'key' => 'dispute',
                'name' => 'Dispute Deal',
                'required_role' => '',
            ],
            [
                'from' => 'escrow_active',
                'to' => 'disputed',
                'key' => 'dispute',
                'name' => 'Dispute Deal',
                'required_role' => '',
            ],
            [
                'from' => 'seller_processing',
                'to' => 'disputed',
                'key' => 'dispute',
                'name' => 'Dispute Deal',
                'required_role' => '',
            ],
            [
                'from' => 'disputed',
                'to' => 'completed',
                'key' => 'close',
                'name' => 'Close Deal',
                'required_role' => '',
            ],
            [
                'from' => 'disputed',
                'to' => 'refunded',
                'key' => 'refund',
                'name' => 'Refund Deal',
                'required_role' => '',
            ],
        ];

        foreach ($transitions as $transitionData) {
            $fromState = $states[$transitionData['from']];
            $toState = $states[$transitionData['to']];

            WorkflowTransition::firstOrCreate(
                ['workflow_definition_id' => $definition->id, 'key' => $transitionData['key']],
                [
                    'uuid' => Str::uuid()->toString(),
                    'workflow_definition_id' => $definition->id,
                    'from_state_id' => $fromState->id,
                    'to_state_id' => $toState->id,
                    'key' => $transitionData['key'],
                    'name' => $transitionData['name'],
                    'required_role' => $transitionData['required_role'],
                    'action' => $transitionData['key'] ?? $transitionData['name'] ?? 'transition',
                    'is_active' => true,
                ]
            );
        }
    }
}
