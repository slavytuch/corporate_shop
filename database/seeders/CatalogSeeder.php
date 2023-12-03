<?php

namespace Database\Seeders;

use App\Models\PriceType;
use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CatalogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $priceTypes = PriceType::all();

        $productList = [];
        $productList[] = Product::updateOrCreate([
            'name' => 'Лого интенсы',
            'description' => 'Просто тестовое лого интенсы',
            'picture' => storage_path('app/public/big_8de42a26c42c8592eb71a42c9776e6f9.jpg')
        ]);

        $productList[] = Product::updateOrCreate([
            'name' => 'Толстовка с РБК',
            'description' => 'Толстовка с надписью "Никогда такого не было, и вот **ять',
            'picture' => storage_path('app/public/346862102790517.webp')
        ]);

        $productList[] = Product::updateOrCreate([
            'name' => 'Футболка "Яндекс"',
            'description' => 'Жёлтая футболка с надписью "Яндекс"',
            'picture' => storage_path('app/public/orig.webp')
        ]);

        foreach ($productList as $product) {
            foreach ($priceTypes as $priceType) {
                $priceList = $product->prices()->pluck('price_type_id');

                if($priceList->contains($priceType->id)) {
                    continue;
                }


                $product->prices()->attach($priceType->id, [
                    'price' => rand(100, 1000),
                ]);
            }
        }
    }
}
