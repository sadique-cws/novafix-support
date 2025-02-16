<?php

namespace Database\Seeders;

use App\Models\Problem;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProblemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        Problem::insert([
            ['name' => 'Power Issue', 'model_id' => 1], // HP 15
            ['name' => 'Display Not Visible', 'model_id' => 2], // Dell XPS 13
            ['name' => 'Dead Device', 'model_id' => 3], // Vivo X40
            ['name' => 'Battery Draining Fast', 'model_id' => 4], // Samsung Galaxy S21
        ]);
    }
}
