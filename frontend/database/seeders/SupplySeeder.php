<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Supply;

class SupplySeeder extends Seeder
{
    public function run(): void
    {
        Supply::insert([

            [
                'name'=>'Papa',
                'category'=>'Tubérculos',
                'unit'=>'Kg',
                'cost'=>2500,
                'stock'=>120,
                'minimum_stock'=>20,
                'is_available'=>true
            ],

            [
                'name'=>'Pan',
                'category'=>'Panadería',
                'unit'=>'Unidades',
                'cost'=>700,
                'stock'=>200,
                'minimum_stock'=>30,
                'is_available'=>true
            ],

            [
                'name'=>'Carne de Res',
                'category'=>'Carnes',
                'unit'=>'Kg',
                'cost'=>18000,
                'stock'=>35,
                'minimum_stock'=>10,
                'is_available'=>true
            ],

            [
                'name'=>'Cerdo',
                'category'=>'Carnes',
                'unit'=>'Kg',
                'cost'=>15000,
                'stock'=>40,
                'minimum_stock'=>10,
                'is_available'=>true
            ],

            [
                'name'=>'Pollo',
                'category'=>'Carnes',
                'unit'=>'Kg',
                'cost'=>13000,
                'stock'=>45,
                'minimum_stock'=>10,
                'is_available'=>true
            ],

            [
                'name'=>'Queso',
                'category'=>'Lácteos',
                'unit'=>'Kg',
                'cost'=>22000,
                'stock'=>18,
                'minimum_stock'=>5,
                'is_available'=>true
            ],

            [
                'name'=>'Salsa BBQ',
                'category'=>'Salsas',
                'unit'=>'Litros',
                'cost'=>12000,
                'stock'=>12,
                'minimum_stock'=>3,
                'is_available'=>true
            ],

            [
                'name'=>'Mayonesa',
                'category'=>'Salsas',
                'unit'=>'Litros',
                'cost'=>10000,
                'stock'=>10,
                'minimum_stock'=>3,
                'is_available'=>true
            ],

            [
                'name'=>'Tomate',
                'category'=>'Salsas',
                'unit'=>'Litros',
                'cost'=>9000,
                'stock'=>8,
                'minimum_stock'=>2,
                'is_available'=>true
            ],



        ]);
    }
}