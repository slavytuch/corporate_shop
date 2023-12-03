<?php

namespace Database\Seeders;

use App\Models\User;
use App\Slavytuch\Shop\Global\Enums\UserRole;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::updateOrCreate([
            'telegram_id' => 406953956,
            'telegram_username' => 'slavytuch0',
            'name' => 'Слава',
            'chat_id' => 406953956
        ]);

        $admin->assignRole(UserRole::ADMIN->value);

        $manager = User::updateOrCreate([
            'telegram_id' => 1507641420,
            'name' => 'Лара',
            'chat_id' => 1507641420
        ]);

        $manager->assignRole(UserRole::MANAGER->value);
    }
}
