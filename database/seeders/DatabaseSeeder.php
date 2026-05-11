<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
        ]);

        $platformAdmin = User::firstOrCreate(
            ['email' => 'platform@example.com'],
            [
                'name' => 'Администратор',
                'password' => bcrypt('password'),
                'organization_id' => null,
                'client_type' => null,
            ],
        );
        $platformAdmin->update(['name' => 'Администратор']);
        if (! $platformAdmin->hasRole('admin')) {
            $platformAdmin->assignRole('admin');
        }

        $org = Organization::firstOrCreate(
            ['slug' => 'demo-beauty-salon'],
            [
                'name' => 'Demo Beauty Salon',
                'plan_name' => 'free',
                'subscription_active_until' => now()->addMonth(),
            ],
        );

        Store::firstOrCreate(
            [
                'organization_id' => $org->id,
                'name' => 'Основная точка',
            ],
            [
                'address' => 'г. Минск, ул. Примерная, д. 1',
                'phone' => '+375291234567',
                'opening_hours' => 'Пн–Вс 10:00–20:00',
            ],
        );

        $businessUser = User::firstOrCreate(
            ['email' => 'salon@example.com'],
            [
                'name' => 'Salon Admin',
                'password' => bcrypt('password'),
                'organization_id' => $org->id,
                'client_type' => 'business',
            ],
        );
        $businessUser->update(['organization_id' => $org->id, 'client_type' => 'business']);
        if (! $businessUser->hasRole('business')) {
            $businessUser->assignRole('business');
        }

        $this->call([
            GiftCertificateSeeder::class,
        ]);
    }
}
