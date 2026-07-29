<?php

namespace Modules\Advertisements\Enums;

enum AdvertisementVisibility: string
{
    case Public = 'Public';
    case Private = 'Private';
    case Hidden = 'Hidden';

    public static function fromValue(?string $value): self
    {
        return self::tryFrom($value ?? self::Public->value) ?? self::Public;
    }
}
