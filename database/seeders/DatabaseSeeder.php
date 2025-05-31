<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Product;
use App\Models\Supplier;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('admin123'),
        ]);

        Product::create([
            'sku' => 'SALP-SWISS-PARIS',
            'name' => 'SALEP SWISS PARIS LOTION PENGHILANG KUTIL',
            'stock' => 0,
            'description' => 'SALEP SWISS PARIS LOTION PENGHILANG KUTIL',
        ]);

        Supplier::create([
            'name' => 'Shopee - SevnStore',
        ]);
    }
}
