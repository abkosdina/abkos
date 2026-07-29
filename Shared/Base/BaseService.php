<?php

namespace Shared\Base;

abstract class BaseService
{
    public function execute(...$arguments): mixed
    {
        return $this->handle(...$arguments);
    }

    abstract protected function handle(...$arguments): mixed;
}
