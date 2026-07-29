<?php

namespace Modules\UserManagement\DTO;

class PermissionDTO
{
    public function __construct(
        public string $name,
        public ?string $group = null,
        public ?string $description = null,
    ) {
    }
}
