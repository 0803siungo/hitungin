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
            'sku' => 'EYB-LA-724',
            'name' => 'Eyebrow Lameila 724 / Mascara Ungu',
            'stock' => 0,
            'price' => 6000,
            'description' => 'Mascara Ungu Merk Lameila SVMY UNGU',
        ]);

        Supplier::create([
            'name' => 'Shopee - SevnStore',
        ]);
    }
}
