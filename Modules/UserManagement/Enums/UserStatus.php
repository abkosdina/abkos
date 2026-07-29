<?php

namespace Modules\UserManagement\Enums;

enum UserStatus: string
{
    case PENDING = 'pending';
    case ACTIVE = 'active';
    case SUSPENDED = 'suspended';
    case BLOCKED = 'blocked';
    case DELETED = 'deleted';
    case WAITING_FOR_KYC = 'waiting_for_kyc';
    case REJECTED = 'rejected';
}
