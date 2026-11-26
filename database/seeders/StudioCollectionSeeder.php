<?php

namespace Database\Seeders;

use App\Models\Template;
use Illuminate\Database\Seeder;

class StudioCollectionSeeder extends Seeder
{
    public function run(): void
    {
        $collection = [
            ['Floral Ivory', 'floral-ivory', 'floral', 'free'],
            ['Zaitun Toscana', 'zaitun-toscana', 'garden', 'free'],
            ['Anggrek Amerta', 'anggrek-amerta', 'floral', 'free'],
            ['Gerbang Bali', 'gerbang-bali', 'heritage', 'free'],
            ['Rembulan Malam', 'rembulan-malam', 'classic', 'free'],
            ['Peoni Merah', 'peoni-merah', 'floral', 'free'],
            ['Art Deco Emas', 'art-deco-emas', 'luxury', 'free'],
            ['Taman Tropis', 'taman-tropis', 'garden', 'free'],
            ['Wisteria Pagi', 'wisteria-pagi', 'floral', 'free'],
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
