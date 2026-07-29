<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Modules\UserManagement\Policies\PermissionPolicy;
use Modules\UserManagement\Policies\RolePolicy;
use Modules\UserManagement\Policies\UserPolicy;
use Modules\Advertisements\Models\Advertisement;
use Modules\Advertisements\Policies\AdvertisementPolicy;
use Modules\Negotiation\Models\Negotiation;
use Modules\Negotiation\Policies\NegotiationPolicy;
use Modules\KYC\Models\KycRequest;
use Modules\KYC\Policies\KycRequestPolicy;
use Modules\Workflow\Models\ApprovalDecision;
use Modules\Workflow\Models\ApprovalDelegation;
use Modules\Workflow\Models\ApprovalInstance;
use Modules\Workflow\Models\ApprovalInstanceStep;
use Modules\Workflow\Policies\ApprovalDecisionPolicy;
use Modules\Workflow\Policies\ApprovalDelegationPolicy;
use Modules\Workflow\Policies\ApprovalInstancePolicy;
use Modules\Workflow\Policies\ApprovalInstanceStepPolicy;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        User::class => UserPolicy::class,
        'Spatie\\Permission\\Models\\Role' => RolePolicy::class,
        'Spatie\\Permission\\Models\\Permission' => PermissionPolicy::class,
        Advertisement::class => AdvertisementPolicy::class,
        Negotiation::class => NegotiationPolicy::class,
        KycRequest::class => KycRequestPolicy::class,
        ApprovalInstance::class => ApprovalInstancePolicy::class,
        ApprovalInstanceStep::class => ApprovalInstanceStepPolicy::class,
        ApprovalDecision::class => ApprovalDecisionPolicy::class,
        ApprovalDelegation::class => ApprovalDelegationPolicy::class,
        \Modules\Deals\Models\Deal::class => \Modules\Deals\Policies\DealPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        Gate::before(function (User $user, $ability) {
            return $user->hasRole('Super Admin') ? true : null;
        });
    }
}
