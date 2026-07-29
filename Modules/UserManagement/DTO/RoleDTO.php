<?php

namespace Modules\UserManagement\DTO;

class RoleDTO
{
    public function __construct(
        public string $name,
        public string $displayName,
        public ?string $description = null,
    ) {
    }
}
