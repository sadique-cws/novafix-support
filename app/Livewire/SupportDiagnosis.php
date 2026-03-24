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
use Illuminate\Support\Facades\DB;

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

    public $sourceProblemId = null;
    public $sourceQuestionId = null;
    public $targetAttachToQuestionId = null;
    public $targetAttachBranch = 'yes'; // yes|no|root
    public $modalFirstQuestion = 'sd-first-question';
    public $modalNewQuestion = 'sd-new-question';

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
        $this->dispatch('close-modal', name: $this->modalFirstQuestion);
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
            $this->dispatch('open-modal', name: $this->modalNewQuestion);
        }
    }

    public function openFirstQuestionModal()
    {
        $this->dispatch('open-modal', name: $this->modalFirstQuestion);
    }

    public function openNewQuestionModal()
    {
        if (!$this->newQuestionAnswer) {
            session()->flash('error', 'Answer a question (YES/NO) to choose which branch to add.');
            return;
        }

        $this->dispatch('open-modal', name: $this->modalNewQuestion);
    }

    public function cancelFirstQuestion()
    {
        $this->newQuestionText = '';
        $this->dispatch('close-modal', name: $this->modalFirstQuestion);
    }

    public function cancelNewQuestion()
    {
        $this->newQuestionText = '';
        $this->newQuestionAnswer = null;
        $this->dispatch('close-modal', name: $this->modalNewQuestion);
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

        $this->dispatch('close-modal', name: $this->modalNewQuestion);
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

    public function cloneFlow()
    {
        $this->validate([
            'sourceProblemId' => 'required|exists:problems,id',
            'sourceQuestionId' => 'required|exists:questions,id',
            'selectedProblem' => 'required|exists:problems,id',
            'targetAttachBranch' => 'required|in:yes,no,root',
        ]);

        $sourceQuestion = Question::query()
            ->whereKey($this->sourceQuestionId)
            ->where('problem_id', $this->sourceProblemId)
            ->first();

        if (!$sourceQuestion) {
            session()->flash('error', 'Source question not found for the selected source problem.');
            return;
        }

        $targetProblemId = (int) $this->selectedProblem;

        try {
            DB::transaction(function () use ($targetProblemId) {
                $idMap = [];
                $visiting = [];
                $clonedRootId = $this->cloneQuestionSubtree((int) $this->sourceQuestionId, $targetProblemId, $idMap, $visiting);

                if ($this->targetAttachBranch === 'root') {
                    $hasExisting = Question::query()->where('problem_id', $targetProblemId)->exists();
                    $newCount = count($idMap);

                    // If the only questions in the target are the newly created ones, allow root.
                    if ($hasExisting && $newCount > 0) {
                        // If there were existing questions, then exists() would be true even before cloning,
                        // but we can't easily distinguish here without extra queries. Keep it strict:
                        // only allow 'root' when target had no questions.
                        $existingBefore = Question::query()
                            ->where('problem_id', $targetProblemId)
                            ->whereNotIn('id', array_values($idMap))
                            ->exists();

                        if ($existingBefore) {
                            throw new \RuntimeException('Target problem already has questions. Attach to a Yes/No branch instead of Root.');
                        }
                    }

                    $this->currentQuestion = Question::find($clonedRootId);
                    $this->newQuestionAnswer = null;
                    $this->newQuestionText = '';
                    $this->loadQuestionTree();
                    return;
                }

                if (!$this->targetAttachToQuestionId && $this->currentQuestion) {
                    $this->targetAttachToQuestionId = $this->currentQuestion->id;
                }

                $this->validate([
                    'targetAttachToQuestionId' => 'required|exists:questions,id',
                ]);

                $targetAttachQuestion = Question::query()
                    ->whereKey($this->targetAttachToQuestionId)
                    ->where('problem_id', $targetProblemId)
                    ->first();

                if (!$targetAttachQuestion) {
                    throw new \RuntimeException('Target attach question not found for the selected target problem.');
                }

                if ($this->targetAttachBranch === 'yes') {
                    if ($targetAttachQuestion->yes_question_id) {
                        throw new \RuntimeException('Target question already has a YES branch. Clear it before attaching a cloned flow.');
                    }
                    $targetAttachQuestion->yes_question_id = $clonedRootId;
                } else {
                    if ($targetAttachQuestion->no_question_id) {
                        throw new \RuntimeException('Target question already has a NO branch. Clear it before attaching a cloned flow.');
                    }
                    $targetAttachQuestion->no_question_id = $clonedRootId;
                }

                $targetAttachQuestion->save();

                $this->currentQuestion = $targetAttachQuestion->fresh();
                $this->newQuestionAnswer = null;
                $this->newQuestionText = '';
                $this->loadQuestionTree();
            });
        } catch (\Throwable $e) {
            session()->flash('error', $e->getMessage());
            return;
        }

        session()->flash('message', 'Flow cloned and attached successfully.');
    }

    private function cloneQuestionSubtree(int $sourceQuestionId, int $targetProblemId, array &$idMap, array &$visiting): int
    {
        if (isset($idMap[$sourceQuestionId])) {
            return $idMap[$sourceQuestionId];
        }

        if (isset($visiting[$sourceQuestionId])) {
            throw new \RuntimeException('Cycle detected in the source flow. Cannot clone.');
        }

        $visiting[$sourceQuestionId] = true;

        $source = Question::find($sourceQuestionId);
        if (!$source) {
            throw new \RuntimeException("Source question {$sourceQuestionId} not found.");
        }

        $copy = Question::create([
            'problem_id' => $targetProblemId,
            'question_text' => $source->question_text,
            'yes_question_id' => null,
            'no_question_id' => null,
        ]);

        $idMap[$sourceQuestionId] = $copy->id;

        if ($source->yes_question_id) {
            $copy->yes_question_id = $this->cloneQuestionSubtree((int) $source->yes_question_id, $targetProblemId, $idMap, $visiting);
        }
        if ($source->no_question_id) {
            $copy->no_question_id = $this->cloneQuestionSubtree((int) $source->no_question_id, $targetProblemId, $idMap, $visiting);
        }

        $copy->save();

        unset($visiting[$sourceQuestionId]);

        return $copy->id;
    }

    public function render()
    {
        $problems = Problem::query()->orderBy('name')->get();
        $sourceQuestions = [];
        $targetQuestions = [];

        if ($this->sourceProblemId) {
            $sourceQuestions = Question::query()
                ->where('problem_id', $this->sourceProblemId)
                ->orderBy('id')
                ->get();
        }

        if ($this->selectedProblem) {
            $targetQuestions = Question::query()
                ->where('problem_id', $this->selectedProblem)
                ->orderBy('id')
                ->get();
        }

        return view('livewire.support-diagnosis', [
            'showCreateFirst' => !$this->currentQuestion,
            'showAddQuestion' => $this->currentQuestion && (!$this->currentQuestion->yes_question_id || !$this->currentQuestion->no_question_id),
            'problemsList' => $problems,
            'sourceQuestions' => $sourceQuestions,
            'targetQuestions' => $targetQuestions,
        ]);
    }
}
