<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $org = Organization::create([
            'name' => 'Demo Beauty Salon',
            'slug' => 'demo-beauty-salon',
            'plan_name' => 'demo',
            'subscription_active_until' => now()->addMonth(),
        ]);

        User::factory()->create([
            'organization_id' => $org->id,
            'name' => 'Admin',
            'email' => 'admin@example.com',
        ]);

        $this->call([
            GiftCertificateSeeder::class,
        ]);
    }
}
