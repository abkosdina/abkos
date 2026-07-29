<?php

namespace Shared\Base;

abstract class BaseAction
{
    public function execute(...$arguments): mixed
    {
        return $this->handle(...$arguments);
    }

    abstract protected function handle(...$arguments): mixed;
}
