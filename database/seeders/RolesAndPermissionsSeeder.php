<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Сброс кэша прав
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

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

        // Создаем все права
        foreach (array_merge($clientPermissions, $businessPermissions) as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Создаем роли
        $clientRole = Role::create(['name' => 'client']);
        $businessRole = Role::create(['name' => 'business']);
        $adminRole = Role::create(['name' => 'admin']);

        // Назначаем права ролям
        $clientRole->givePermissionTo($clientPermissions);
        $businessRole->givePermissionTo($businessPermissions);
        $adminRole->givePermissionTo(Permission::all());
    }
}
