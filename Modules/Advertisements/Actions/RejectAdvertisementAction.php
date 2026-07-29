<?php

namespace Modules\Advertisements\Actions;

use App\Models\User;
use Modules\Advertisements\Services\AdvertisementWorkflowService;

class RejectAdvertisementAction
{
    public function __construct(protected AdvertisementWorkflowService $workflow)
    {
    }

    public function execute(User $user, $advertisement, array $payload = []): bool
    {
        return $this->workflow->applyTransition($user, $advertisement, 'reject', $payload);
    }
}
