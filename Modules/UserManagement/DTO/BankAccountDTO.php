<?php

namespace Modules\UserManagement\DTO;

class BankAccountDTO
{
    public function __construct(
        public string $bankName,
        public string $accountHolderName,
        public ?string $accountNumber = null,
        public ?string $iban = null,
        public bool $isDefault = false,
    ) {
    }
}
