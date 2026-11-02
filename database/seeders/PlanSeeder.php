<?php

namespace Database\Seeders;

use App\Models\Plan;
use App\Models\PlanFeature;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Free',
                'slug' => 'free',
                'description' => 'Paket gratis untuk membuat undangan digital sederhana.',
                'price' => 0,
                'currency' => 'IDR',
                'billing_period' => 'free',
                'is_active' => true,

                'features' => [
                    'max_invitations' => 1,
                    'max_gallery_images' => 5,
                    'premium_templates' => false,
                    'watermark' => true,
                    'music_upload' => true,
                    'rsvp_enabled' => true,
                    'guestbook_enabled' => true,
                    'custom_slug' => true,
                ],
            ],

            [
                'name' => 'Premium',
                'slug' => 'premium',
                'description' => 'Paket premium dengan fitur dan pilihan desain lebih lengkap.',
                'price' => 0,
                'currency' => 'IDR',
                'billing_period' => 'monthly',
                'is_active' => true,

                'features' => [
                    'max_invitations' => 10,
                    'max_gallery_images' => 30,
                    'premium_templates' => true,
                    'watermark' => false,
                    'music_upload' => true,
                    'rsvp_enabled' => true,
                    'guestbook_enabled' => true,
                    'custom_slug' => true,
                ],
            ],
        ];

        foreach ($plans as $data) {
            $features = $data['features'];

            unset($data['features']);

            $plan = Plan::updateOrCreate(
                ['slug' => $data['slug']],
                $data
            );

            foreach ($features as $key => $value) {
                PlanFeature::updateOrCreate(
                    [
                        'plan_id' => $plan->id,
                        'feature_key' => $key,
                    ],
                    [
                        'feature_value' => [
                            'value' => $value,
                        ],
                    ]
                );
            }
        }
    }
}
