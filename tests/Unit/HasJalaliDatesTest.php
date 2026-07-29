<?php

namespace Tests\Unit;

use Modules\Advertisements\Models\Advertisement;
use Modules\Advertisements\Models\AdvertisementContact;
use Tests\TestCase;

class HasJalaliDatesTest extends TestCase
{
    public function test_advertisement_model_can_be_instantiated_without_trait_property_conflict(): void
    {
        $advertisement = new Advertisement();

        $this->assertInstanceOf(Advertisement::class, $advertisement);
    }

    public function test_advertisement_contact_model_can_be_instantiated_without_trait_property_conflict(): void
    {
        $contact = new AdvertisementContact();

        $this->assertInstanceOf(AdvertisementContact::class, $contact);
    }
}
