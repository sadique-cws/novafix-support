<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Device;
use App\Models\Brand;
use App\Models\Model as ModelTable;
use App\Models\Problem;
use App\Models\Question;
use App\Models\UserAnswer;
use Auth;

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
    public $newQuestionAnswer;

    public $editingQuestionId = null;
    public $editingQuestionText = '';

    public function mount()
    {
        $this->devices = Device::all();
    }

    public function editQuestion($questionId)
    {
        $question = Question::find($questionId);
        if ($question) {
            $this->editingQuestionId = $questionId;
            $this->editingQuestionText = $question->question_text;
        }
    }

    public function updateQuestion()
    {
        $this->validate(['editingQuestionText' => 'required|min:3']);

        $question = Question::find($this->editingQuestionId);
        if (!$question) {
            session()->flash('error', 'Question not found.');
            return;
        }

        $question->update(['question_text' => $this->editingQuestionText]);

        $this->editingQuestionId = null;
        $this->editingQuestionText = '';
        $this->currentQuestion = $question;

        session()->flash('message', 'Question updated successfully.');
    }

    public function cancelEdit()
    {
        $this->editingQuestionId = null;
        $this->editingQuestionText = '';
    }

    public function updatedSelectedDevice()
    {
        $this->brands = Brand::where('device_id', $this->selectedDevice)->get();
        $this->resetSelections(['brand', 'model', 'problem']);
    }

    public function updatedSelectedBrand()
    {
        $this->models = ModelTable::where('brand_id', $this->selectedBrand)->get();
        $this->resetSelections(['model', 'problem']);
    }

    public function updatedSelectedModel()
    {
        $this->problems = Problem::where('model_id', $this->selectedModel)->get();
        $this->resetSelections(['problem']);
    }

    public function updatedSelectedProblem()
    {
        $this->currentQuestion = Question::where('problem_id', $this->selectedProblem)->first();
        if ($this->currentQuestion) {
            $this->loadQuestionTree();
        }
    }

    public function createFirstQuestion()
    {
        $this->validate(['newQuestionText' => 'required|min:3']);

        $this->currentQuestion = Question::create([
            'problem_id' => $this->selectedProblem,
            'question_text' => $this->newQuestionText,
        ]);
        $this->newQuestionText = '';
        $this->loadQuestionTree();
    }

    public function loadQuestionTree()
    {
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

    public function previousQuestion()
    {
        // Find the parent question where the current question appears in yes_question_id or no_question_id
        $parentQuestion = Question::where('yes_question_id', $this->currentQuestion->id)
            ->orWhere('no_question_id', $this->currentQuestion->id)
            ->first();

        // If a parent question exists, update the current question
        if ($parentQuestion) {
            $this->currentQuestion = $parentQuestion;
        }
    }


    public function answerQuestion($answer)
    {
        if (!$this->currentQuestion) return;

        UserAnswer::create([
            'user_id' => Auth::id(),
            'question_id' => $this->currentQuestion->id,
            'device_id' => $this->selectedDevice,
            'brand_id' => $this->selectedBrand,
            'model_id' => $this->selectedModel,
            'problem_id' => $this->selectedProblem,
            'selected_answer' => $answer,
        ]);

        if ($answer === 'yes' && $this->currentQuestion->yes_question_id) {
            $this->currentQuestion = Question::find($this->currentQuestion->yes_question_id);
        } elseif ($answer === 'no' && $this->currentQuestion->no_question_id) {
            $this->currentQuestion = Question::find($this->currentQuestion->no_question_id);
        } else {
            $this->newQuestionText = '';
            $this->newQuestionAnswer = $answer;
        }
    }

    public function createQuestion()
    {
        $this->validate(['newQuestionText' => 'required|string']);

        $newQuestion = Question::create([
            'problem_id' => $this->selectedProblem,
            'question_text' => $this->newQuestionText,
        ]);

        if ($this->newQuestionAnswer === 'yes') {
            $this->currentQuestion->yes_question_id = $newQuestion->id;
        } else {
            $this->currentQuestion->no_question_id = $newQuestion->id;
        }

        $this->currentQuestion->save();

        if (!$this->currentQuestion->yes_question_id || !$this->currentQuestion->no_question_id) {
            $this->newQuestionAnswer = $this->newQuestionAnswer === 'yes' ? 'no' : null;
            $this->newQuestionText = '';
        } else {
            $this->currentQuestion = Question::find($this->currentQuestion->id);
            $this->newQuestionAnswer = null;
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

    private function resetSelections($fields)
    {
        foreach ($fields as $field) {
            $this->{'selected' . ucfirst($field)} = null;
        }
    }

    public function render()
    {
        return view('livewire.support-diagnosis', [
            'showCreateFirst' => !$this->currentQuestion,
            'showAddQuestion' => $this->currentQuestion && (!$this->currentQuestion->yes_question_id || !$this->currentQuestion->no_question_id),
        ]);
    }
}
