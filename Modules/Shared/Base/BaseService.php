<?php

namespace Modules\Shared\Base;

use Modules\Shared\Contracts\ServiceInterface;

abstract class BaseService implements ServiceInterface
{
    public function execute(...$arguments): mixed
    {
        return $this->handle(...$arguments);
    }

    abstract protected function handle(...$arguments): mixed;
}
