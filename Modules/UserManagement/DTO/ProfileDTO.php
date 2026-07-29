<?php

namespace Modules\UserManagement\DTO;

class ProfileDTO
{
    public function __construct(
        public ?string $firstName = null,
        public ?string $lastName = null,
        public ?string $nationalCode = null,
        public ?string $mobile = null,
    ) {
    }
}
