<?php

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        Brand::insert([
            ['name' => 'HP', 'device_id' => 1], // Laptop
            ['name' => 'Dell', 'device_id' => 1], // Laptop
            ['name' => 'Vivo', 'device_id' => 2], // Mobile
            ['name' => 'Samsung', 'device_id' => 2], // Mobile
        ]);
    }
}
