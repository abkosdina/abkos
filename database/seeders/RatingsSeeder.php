<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Rating; // if exists; else use DB
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RatingsSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::where('email', 'like', 'ads_test_user_%@example.com')->get();
        foreach ($users as $user) {
            // create 3 ratings for each user from other test users
            $other = User::where('id', '!=', $user->id)->inRandomOrder()->take(3)->get();
            foreach ($other as $from) {
                DB::table('ratings')->updateOrInsert([
                    'from_user_id' => $from->id,
                    'to_user_id' => $user->id,
                    'order_id' => null,
                ], [
                    'uuid' => (string) \Illuminate\Support\Str::uuid(),
                    'score' => rand(3, 5),
                    'comment' => 'Seeded rating',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
