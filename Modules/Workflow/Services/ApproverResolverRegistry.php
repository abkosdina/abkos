<?php

namespace Modules\Workflow\Services;

use Illuminate\Database\Eloquent\Collection;
use Modules\Workflow\Interfaces\ApproverResolverInterface;
use Modules\Workflow\Models\ApprovalInstance;
use Modules\Workflow\Models\ApprovalInstanceStep;
use Modules\Workflow\Models\ApprovalStep;

class ApproverResolverRegistry
{
    /** @var array<int, ApproverResolverInterface> */
    protected array $resolvers = [];

    public function register(ApproverResolverInterface $resolver): self
    {
        $this->resolvers[] = $resolver;

        return $this;
    }

    public function resolve(ApprovalInstance $approvalInstance, ApprovalInstanceStep $step): Collection
    {
        foreach ($this->resolvers as $resolver) {
            if ($resolver->supports($step->approvalStep)) {
                return $resolver->resolve($approvalInstance, $step);
            }
        }

        return new Collection();
    }

    public function findResolver(ApprovalStep $step): ?ApproverResolverInterface
    {
        foreach ($this->resolvers as $resolver) {
            if ($resolver->supports($step)) {
                return $resolver;
            }
        }

        return null;
    }
}
