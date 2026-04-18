<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Сброс кэша прав
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Создаем права для покупателей
        $clientPermissions = [
            'view certificates',
            'purchase certificates',
            'view own certificates',
            'split certificates',
        ];

        // Создаем права для бизнеса
        $businessPermissions = [
            'manage certificates',
            'create certificates',
            'edit certificates',
            'delete certificates',
            'view analytics',
            'manage locations',
            'manage customers',
            'manage products',
            'manage orders',
            'redeem certificates',
        ];

        foreach (array_merge($clientPermissions, $businessPermissions) as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $clientRole = Role::firstOrCreate(['name' => 'client', 'guard_name' => 'web']);
        $businessRole = Role::firstOrCreate(['name' => 'business', 'guard_name' => 'web']);
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

        $clientRole->syncPermissions($clientPermissions);
        $businessRole->syncPermissions($businessPermissions);
        $adminRole->syncPermissions(Permission::where('guard_name', 'web')->get());
    }
}
