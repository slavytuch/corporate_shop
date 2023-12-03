<?php

namespace Database\Seeders;

use App\Models\PriceType;
use App\Models\User;
use Illuminate\Database\Seeder;

class BalanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $priceTypes = PriceType::all();
        $users = User::all();

        foreach ($priceTypes as $balanceType) {
            foreach ($users as $user) {
                $balanceList = $user->balances()->pluck('price_type_id');

                if ($balanceList->contains($balanceType->id)) {
                    continue;
                }

                $user->balances()->create([
                    'amount' => rand(1000, 10000),
                    'price_type_id' => $balanceType->id
                ]);
            }
        }
    }
}
