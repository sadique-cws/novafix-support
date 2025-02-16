<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Device;
use App\Models\Brand;
use App\Models\Model as ModelTable;
use App\Models\Problem;
use App\Models\Question;

class SupportDiagnosis extends Component
{
    public $devices;
    public $brands = [];
    public $models = [];
    public $problems = [];
    public $questions = [];
    public $questionTree = [];


    public $selectedDevice;
    public $selectedBrand;
    public $selectedModel;
    public $selectedProblem;
    public $currentQuestion;

    public $newQuestionText;
    public $newQuestionAnswer; // Stores 'yes' or 'no' for linking the new question

    public $editingQuestionId = null;
    public $editingQuestionText = '';

    public function mount()
    {
        $this->devices = Device::all();
    }


    public function editQuestion($questionId)
    {
        $this->editingQuestionId = $questionId;
        $this->editingQuestionText = Question::find($questionId)->question_text;
    }

    // Update the Question
    public function updateQuestion()
    {
        $this->validate([
            'editingQuestionText' => 'required|min:3',
        ]);

        // Debugging: Check if the ID exists
        $question = Question::find($this->editingQuestionId);

        if (!$question) {
            session()->flash('error', 'Question not found.');
            return;
        }

        // Update the question
        $question->update(['question_text' => $this->editingQuestionText]);

        // Reset variables after update
        $this->editingQuestionId = null;
        $this->editingQuestionText = '';
        $this->currentQuestion = $question;

        session()->flash('message', 'Question updated successfully.');
    }

    // Cancel Editing
    public function cancelEdit()
    {
        $this->editingQuestionId = null;
        $this->editingQuestionText = '';
    }

    public function updatedSelectedDevice()
    {
        $this->brands = Brand::where('device_id', $this->selectedDevice)->get();
        $this->selectedBrand = null;
        $this->models = [];
        $this->problems = [];
    }

    public function updatedSelectedBrand()
    {
        $this->models = ModelTable::where('brand_id', $this->selectedBrand)->get();
        $this->selectedModel = null;
        $this->problems = [];
    }

    public function updatedSelectedModel()
    {
        $this->problems = Problem::where('model_id', $this->selectedModel)->get();
        $this->selectedProblem = null;
    }


    public function updatedSelectedProblem()
    {
        $this->currentQuestion = Question::where('problem_id', $this->selectedProblem)->first();
        $this->loadQuestionTree();
    }

    public function loadQuestionTree()
    {
        if (!$this->selectedProblem) return;

        $this->questionTree = $this->buildTree($this->currentQuestion);
    }

    private function buildTree($question)
    {
        if (!$question) return null;

        return [
            'text' => $question->question_text,
            'yes' => $question->yes_question_id ? $this->buildTree(Question::find($question->yes_question_id)) : null,
            'no' => $question->no_question_id ? $this->buildTree(Question::find($question->no_question_id)) : null,
        ];
    }


    public function answerQuestion($answer)
    {
        if (!$this->currentQuestion) {
            return;
        }

        if ($answer === 'yes' && $this->currentQuestion->yes_question_id) {
            $this->currentQuestion = Question::find($this->currentQuestion->yes_question_id);
        } elseif ($answer === 'no' && $this->currentQuestion->no_question_id) {
            $this->currentQuestion = Question::find($this->currentQuestion->no_question_id);
        } else {
            // No next question, show form to create new question
            $this->newQuestionText = '';
            $this->newQuestionAnswer = $answer;
        }
    }

    public function createQuestion()
    {
        $this->validate([
            'newQuestionText' => 'required|string',
        ]);

        // Create new question
        $newQuestion = Question::create([
            'problem_id' => $this->selectedProblem,
            'question_text' => $this->newQuestionText,
        ]);

        // Link to previous question
        if ($this->newQuestionAnswer === 'yes') {
            $this->currentQuestion->yes_question_id = $newQuestion->id;
        } else {
            $this->currentQuestion->no_question_id = $newQuestion->id;
        }

        $this->currentQuestion->save();

        // Check if we need the NO question next
        if ($this->newQuestionAnswer === 'yes') {
            $this->newQuestionAnswer = 'no'; // Now, create a No answer question
            $this->newQuestionText = ''; // Reset for new input
        } else {
            // If both Yes and No answers are added, move to the question tree
            $this->currentQuestion = Question::find($this->currentQuestion->id);
            $this->newQuestionAnswer = null; // Stop the question creation form
        }
    }

    public function resetSelection()
    {
        $this->selectedDevice = null;
        $this->selectedBrand = null;
        $this->selectedModel = null;
        $this->selectedProblem = null;
        $this->currentQuestion = null;
        $this->newQuestionText = null;
        $this->newQuestionAnswer = null;

        // Reload devices
        $this->devices = Device::all();
        $this->brands = [];
        $this->models = [];
        $this->problems = [];
    }

    public function render()
    {
        return view('livewire.support-diagnosis');
    }
}
