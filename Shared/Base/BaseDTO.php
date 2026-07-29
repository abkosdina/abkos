<?php

namespace Shared\Base;

abstract class BaseDTO
{
    public function __construct(public array $data = [])
    {
    }

    public function toArray(): array
    {
        return $this->data;
    }
}
