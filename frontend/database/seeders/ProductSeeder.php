<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        Product::truncate();

        $products = [
            [
                'name'         => 'Chuzo de Res Tradicional',
                'description'  => 'Carne de res marinada con papas criollas y aji',
                'price'        => 12000,
                'category'     => 'Chuzo de Res',
                'is_available' => true,
                'stock'        => 50,
                'image_url'    => 'https://images.unsplash.com/photo-1529692236671-f1f6cf9683ba?w=600&q=80',
            ],
            [
                'name'         => 'Chuzo de Res Premium',
                'description'  => 'Corte premium con vegetales asados y chimichurri',
                'price'        => 18000,
                'category'     => 'Chuzo de Res',
                'is_available' => true,
                'stock'        => 30,
                'image_url'    => 'https://images.unsplash.com/photo-1544025162-d76694265947?w=600&q=80',
            ],
            [
                'name'         => 'Chuzo de Cerdo Clasico',
                'description'  => 'Cerdo jugoso con arepa y salsas de la casa',
                'price'        => 11000,
                'category'     => 'Chuzo de Cerdo',
                'is_available' => true,
                'stock'        => 40,
                'image_url'    => 'https://images.unsplash.com/photo-1555939594-58d7cb561ad1?w=600&q=80',
            ],
            [
                'name'         => 'Chuzo de Cerdo BBQ',
                'description'  => 'Con salsa BBQ, cebolla caramelizada y tocineta',
                'price'        => 15000,
                'category'     => 'Chuzo de Cerdo',
                'is_available' => true,
                'stock'        => 25,
                'image_url'    => 'https://images.unsplash.com/photo-1558030006-450675393462?w=600&q=80',
            ],
            [
                'name'         => 'Chuzo de Pollo Especiado',
                'description'  => 'Pollo marinado con especias y vegetales frescos',
                'price'        => 10000,
                'category'     => 'Chuzo de Pollo',
                'is_available' => true,
                'stock'        => 60,
                'image_url'    => 'https://images.unsplash.com/photo-1603360946369-dc9bb6258143?w=600&q=80',
            ],
            [
                'name'         => 'Chuzo de Pollo BBQ',
                'description'  => 'Pollo con salsa BBQ y papas fritas',
                'price'        => 13000,
                'category'     => 'Chuzo de Pollo',
                'is_available' => true,
                'stock'        => 45,
                'image_url'    => 'https://images.unsplash.com/photo-1598103442097-8b74394b95c8?w=600&q=80',
            ],
            [
                'name'         => 'Chuzo Mixto',
                'description'  => 'Combinacion de res, cerdo y pollo con guarnicion',
                'price'        => 20000,
                'category'     => 'Mixto',
                'is_available' => true,
                'stock'        => 20,
                'image_url'    => 'https://images.unsplash.com/photo-1625944230945-1b7dd3b949ab?w=600&q=80',
            ],
            [
                'name'         => 'Chuzo Especial de la Casa',
                'description'  => 'Nuestra receta secreta con todos los acompanantes',
                'price'        => 25000,
                'category'     => 'Especiales',
                'is_available' => true,
                'stock'        => 15,
                'image_url'    => 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=600&q=80',
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}