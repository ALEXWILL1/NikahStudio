<?php

namespace Database\Seeders;

use App\Models\Template;
use Illuminate\Database\Seeder;

class TemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'name' => 'Elegant Floral',
                'slug' => 'elegant-floral',
                'description' => 'Desain romantis dengan ornamen bunga dan warna lembut.',
                'preview_image' => null,
                'view_key' => 'elegant-floral',
                'category' => 'floral',
                'tier' => 'free',
                'is_active' => true,

                'config' => [
                    'colors' => [
                        'background' => '#FAF7F2',
                        'primary' => '#A47C69',
                        'secondary' => '#E8D9CF',
                        'text' => '#40352F',
                    ],
                    'fonts' => [
                        'heading' => 'Playfair Display',
                        'body' => 'Lato',
                    ],
                ],
            ],

            [
                'name' => 'Minimalist White',
                'slug' => 'minimalist-white',
                'description' => 'Desain minimalis modern dengan dominasi putih.',
                'preview_image' => null,
                'view_key' => 'minimalist-white',
                'category' => 'minimalist',
                'tier' => 'free',
                'is_active' => true,

                'config' => [
                    'colors' => [
                        'background' => '#FFFFFF',
                        'primary' => '#777777',
                        'secondary' => '#EEEEEE',
                        'text' => '#222222',
                    ],
                    'fonts' => [
                        'heading' => 'Cormorant Garamond',
                        'body' => 'Inter',
                    ],
                ],
            ],

            [
                'name' => 'Modern Luxury',
                'slug' => 'modern-luxury',
                'description' => 'Desain eksklusif dengan perpaduan warna emas dan hitam.',
                'preview_image' => null,
                'view_key' => 'modern-luxury',
                'category' => 'luxury',
                'tier' => 'premium',
                'is_active' => true,

                'config' => [
                    'colors' => [
                        'background' => '#171717',
                        'primary' => '#C6A66B',
                        'secondary' => '#34302B',
                        'text' => '#F8F4EB',
                    ],
                    'fonts' => [
                        'heading' => 'Cinzel',
                        'body' => 'Montserrat',
                    ],
                ],
            ],

            [
                'name' => 'Rustic Wedding',
                'slug' => 'rustic-wedding',
                'description' => 'Desain natural dengan nuansa alam dan warna earthy.',
                'preview_image' => null,
                'view_key' => 'rustic-wedding',
                'category' => 'rustic',
                'tier' => 'premium',
                'is_active' => true,

                'config' => [
                    'colors' => [
                        'background' => '#F3EBDD',
                        'primary' => '#78836A',
                        'secondary' => '#B99B75',
                        'text' => '#443B32',
                    ],
                    'fonts' => [
                        'heading' => 'Playfair Display',
                        'body' => 'Lato',
                    ],
                ],
            ],
        ];

        foreach ($templates as $data) {
            Template::updateOrCreate(
                ['slug' => $data['slug']],
                $data
            );
        }
    }
}
