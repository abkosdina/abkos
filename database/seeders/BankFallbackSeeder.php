<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class BankFallbackSeeder extends Seeder
{
    public function run(): void
    {
        if (! Schema::hasTable('banks')) {
            if ($this->command) {
                $this->command->info('Table "banks" does not exist. Run migrations before seeding.');
            }
            return;
        }

        $banks = [
            ['name' => 'بانک ملی ایران', 'code' => 'melli'],
            ['name' => 'بانک ملت', 'code' => 'mellat'],
            ['name' => 'بانک تجارت', 'code' => 'tejarat'],
            ['name' => 'بانک صادرات ایران', 'code' => 'saderat'],
            ['name' => 'بانک پارسیان', 'code' => 'parsian'],
            ['name' => 'بانک پاسارگاد', 'code' => 'pasargad'],
            ['name' => 'بانک شهر', 'code' => 'shahr'],
            ['name' => 'بانک کشاورزی', 'code' => 'keshavarzi'],
            ['name' => 'بانک توسعه تعاون', 'code' => 'tosee_taavon'],
            ['name' => 'پست بانک ایران', 'code' => 'postbank'],
        ];

        $now = now();

        foreach ($banks as $bank) {
            DB::table('banks')->insertOrIgnore([
                'uuid' => Str::uuid()->toString(),
                'name' => $bank['name'],
                'slug' => Str::slug($bank['code']),
                'code' => $bank['code'],
                'status' => 'active',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
