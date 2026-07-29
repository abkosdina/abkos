<?php

namespace Modules\UserManagement\DTO;

class UserDTO
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
    ) {
    }
}
