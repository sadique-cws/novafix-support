<?php

namespace Database\Seeders;

use App\Models\Question;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class QuestionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        Question::insert([
            [
                'problem_id' => 1, // Power Issue (HP 15)
                'question_text' => 'Is the power adapter plugged in?',
                'yes_question_id' => null, // Next question can be added later
                'no_question_id' => null,
            ],
            [
                'problem_id' => 2, // Display Not Visible (Dell XPS 13)
                'question_text' => 'Do you see any faint image on the screen?',
                'yes_question_id' => null,
                'no_question_id' => null,
            ],
            [
                'problem_id' => 3, // Dead Device (Vivo X40)
                'question_text' => 'Does the phone vibrate when turning on?',
                'yes_question_id' => null,
                'no_question_id' => null,
            ],
            [
                'problem_id' => 4, // Battery Draining Fast (Samsung Galaxy S21)
                'question_text' => 'Have you checked the battery health settings?',
                'yes_question_id' => null,
                'no_question_id' => null,
            ],
        ]);
    }
}
