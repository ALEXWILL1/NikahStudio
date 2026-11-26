<?php

namespace Database\Seeders;

use App\Models\Template;
use Illuminate\Database\Seeder;

class StudioCollectionSeeder extends Seeder
{
    public function run(): void
    {
        $collection = [
            ['Hortensia Biru', 'hortensia-biru', 'floral', 'free'],
            ['Pusaka Toba', 'pusaka-toba', 'heritage', 'premium'],
            ['Beludru Merlot', 'beludru-merlot', 'luxury', 'premium'],
            ['Kebun Senja', 'kebun-senja', 'garden', 'free'],
            ['Janji Champagne', 'janji-champagne', 'classic', 'free'],
        ];

        foreach ($collection as [$name, $slug, $category, $tier]) {
            Template::firstOrCreate(['slug' => $slug], [
                'name' => $name, 'view_key' => $slug, 'category' => $category,
                'tier' => $tier, 'is_active' => true,
            ]);
        }
    }
}
