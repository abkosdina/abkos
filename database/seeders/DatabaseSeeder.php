<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Modules\Advertisements\Database\Seeders\AdvertisementSeeder;
use Modules\Banks\Database\Seeders\BanksModuleSeeder;
use Modules\Deals\Database\Seeders\DealWorkflowDefinitionSeeder;
use Modules\UserManagement\Database\Seeders\RolePermissionSeeder;
use Database\Seeders\BankPlansSeeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );

        $this->call(RolePermissionSeeder::class);

        // Seed admin and customer demo users
        $adminUser = User::updateOrCreate(
            ['mobile' => '09134576502'],
            [
                'name' => 'Admin User',
                'email' => '09134576502@example.com',
                'password' => bcrypt('Password123!'),
                'status' => 'active',
                'is_verified' => true,
                'is_suspended' => false,
                'moderation_note' => 'Admin demo user',
                'email_verified_at' => now(),
            ]
        );
        $adminUser->syncRoles(['administrator', 'Super Admin']);

        $customerUser = User::updateOrCreate(
            ['mobile' => '09103852437'],
            [
                'name' => 'Customer User',
                'email' => '09103852437@example.com',
                'password' => bcrypt('Password123!'),
                'status' => 'active',
                'is_verified' => true,
                'is_suspended' => false,
                'moderation_note' => 'Customer demo user',
                'email_verified_at' => now(),
            ]
        );
        $customerUser->syncRoles(['customer']);

        $this->call(ProvinceSeeder::class);
        $this->call(BankFallbackSeeder::class);
        $this->call(BanksModuleSeeder::class);
        $this->call(BankPlansSeeder::class);
        $this->call(AdvertisementSeeder::class);
        $this->call(DealWorkflowDefinitionSeeder::class);
        $this->call(RatingsSeeder::class);
        $this->call(\Modules\Chat\Database\Seeders\ChatModuleSeeder::class);
    }
}
