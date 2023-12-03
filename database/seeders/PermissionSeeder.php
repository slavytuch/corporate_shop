<?php

namespace Database\Seeders;

use App\Slavytuch\Shop\Global\Enums\UserPermissions;
use App\Slavytuch\Shop\Global\Enums\UserRole;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminRole = Role::firstOrCreate(['name' => UserRole::ADMIN->value]);
        $permissionSetManager = Permission::firstOrCreate(['name' => UserPermissions::SET_MANAGER->value]);
        $permissionAccessAllOrders = Permission::firstOrCreate(['name' => UserPermissions::ACCESS_ALL_ORDERS->value]);
        $permissionAccessCatalog = Permission::firstOrCreate(['name' => UserPermissions::ACCESS_CATALOG->value]);

        $adminRole->givePermissionTo([$permissionSetManager, $permissionAccessCatalog, $permissionAccessAllOrders]);

        $managerRole = Role::firstOrCreate(['name' => UserRole::MANAGER->value]);

        $managerRole->givePermissionTo([$permissionAccessCatalog, $permissionAccessAllOrders]);
    }
}
