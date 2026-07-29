<?php

namespace Modules\Shared\Contracts;

interface ServiceInterface
{
    public function execute(...$arguments): mixed;
}
