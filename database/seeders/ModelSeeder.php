<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Model as ModelTable;

class ModelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        ModelTable::insert([
            ['name' => 'HP 15', 'brand_id' => 1],
            ['name' => 'Dell XPS 13', 'brand_id' => 2],
            ['name' => 'Vivo X40', 'brand_id' => 3],
            ['name' => 'Samsung Galaxy S21', 'brand_id' => 4],
        ]);
    }
}
