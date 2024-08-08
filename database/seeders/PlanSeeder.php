<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Plan::insert([
            [
                "name" => 'Golden',
                'price' => 95, // usd
                'stripe_price_id' => 'price_',
                'features' => json_encode(['Up to 10 settings', 'Up to 1000 notifications per month']),
            ],
            [
                "name" => 'Ultimate',
                'price' => 169, // usd
                'stripe_price_id' => 'price_',
                'features' => json_encode(['Up to 99 settings', 'Up to 9999 notifications per month']),
            ],
        ]);
    }
}
