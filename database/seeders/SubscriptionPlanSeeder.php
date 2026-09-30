<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class SubscriptionPlanSeeder extends Seeder
{
    public function run(): void
    {
        SubscriptionPlan::updateOrCreate(
            ['plan_code' => 'trial_7d'],
            [
                'name' => '7-Day Trial',
                'term_type' => 'trial',
                'duration_days' => 7,
                'price' => 0,
                'currency' => 'AED',
                'is_active' => true,
                'sort_order' => 1,
                'description' => 'Seven-day trial, activated by a platform admin.',
            ]
        );

        SubscriptionPlan::updateOrCreate(
            ['plan_code' => 'monthly'],
            [
                'name' => 'Monthly',
                'term_type' => 'monthly',
                'duration_days' => null,
                'price' => 0,
                'currency' => 'AED',
                'is_active' => false,
                'sort_order' => 2,
                'description' => 'Monthly clinic license.',
            ]
        );

        SubscriptionPlan::updateOrCreate(
            ['plan_code' => 'yearly'],
            [
                'name' => 'Yearly',
                'term_type' => 'yearly',
                'duration_days' => null,
                'price' => 0,
                'currency' => 'AED',
                'is_active' => false,
                'sort_order' => 3,
                'description' => 'Yearly clinic license.',
            ]
        );

        SubscriptionPlan::updateOrCreate(
            ['plan_code' => 'lifetime'],
            [
                'name' => 'Lifetime',
                'term_type' => 'lifetime',
                'duration_days' => null,
                'price' => 0,
                'currency' => 'AED',
                'is_active' => false,
                'sort_order' => 4,
                'description' => 'Lifetime clinic license.',
            ]
        );
    }
}