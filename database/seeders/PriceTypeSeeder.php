<?php

namespace Database\Seeders;

use App\Models\PriceType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PriceTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PriceType::firstOrCreate([
            'name' => 'Интенса'
        ]);

        PriceType::firstOrCreate([
            'name' => 'Зимняя валюта'
        ]);
    }
}
