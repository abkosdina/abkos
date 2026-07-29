<?php

namespace Modules\Advertisements\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Advertisements\Database\Factories\AdvertisementFactory;
use Modules\Advertisements\Models\Advertisement;

class AdvertisementsTableSeeder extends Seeder
{
    public function run(): void
    {
        // Create 50 published advertisements
        Advertisement::factory(50)
            ->published()
            ->create();
    }
}
