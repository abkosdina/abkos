<?php

namespace Modules\Shared\Base;

abstract class BaseDTO
{
    public function __construct(protected array $data = [])
    {
    }

    public function toArray(): array
    {
        return $this->data;
    }
}
