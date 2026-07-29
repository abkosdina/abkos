<?php

namespace Modules\Advertisements\Enums;

enum AdvertisementStatus: string
{
    case Draft = 'Draft';
    case PendingReview = 'PendingReview';
    case NeedCorrection = 'NeedCorrection';
    case Rejected = 'Rejected';
    case Approved = 'Approved';
    case Published = 'Published';
    case Paused = 'Paused';
    case Expired = 'Expired';
    case Sold = 'Sold';
    case Archived = 'Archived';
    case Deleted = 'Deleted';

    public static function fromValue(?string $value): self
    {
        return self::tryFrom($value ?? self::Draft->value) ?? self::Draft;
    }

    public function label(): string
    {
        return $this->value;
    }
}
