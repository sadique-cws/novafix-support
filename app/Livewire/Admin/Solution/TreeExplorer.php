<?php

namespace App\Livewire\Admin\Solution;

use App\Models\Brand;
use App\Models\Device;
use App\Models\Model;
use App\Models\Problem;
use App\Models\Question;
use App\Services\QuestionFlowCloner;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin-layout')]
class TreeExplorer extends Component
{
    public $devices;
    public $brands = [];
    public $models = [];
    public $problems = [];

    public $selectedDevice;
    public $selectedBrand;
    public $selectedModel;
    public $selectedProblem;

    public $tree = [
        'problem' => null,
        'roots' => [],
        'nodes' => [],
        'count' => 0,
    ];

    // clone form
    public $sourceProblemId;
    public $sourceQuestionId;
    public $attachMode = 'yes'; // yes|no|root
    public $targetAttachQuestionId;
    public $cloneSearch = '';

    public function mount()
    {
        $this->devices = Device::query()->orderBy('name')->get();
    }

    public function updatedSelectedDevice()
    {
        $this->brands = Brand::query()->where('device_id', $this->selectedDevice)->orderBy('name')->get();
        $this->selectedBrand = null;
        $this->models = [];
        $this->problems = [];
        $this->selectedModel = null;
        $this->selectedProblem = null;
        $this->resetTree();
    }

    public function updatedSelectedBrand()
    {
        $this->models = Model::query()->where('brand_id', $this->selectedBrand)->orderBy('name')->get();
        $this->selectedModel = null;
        $this->problems = [];
        $this->selectedProblem = null;
        $this->resetTree();
    }

    public function updatedSelectedModel()
    {
        $this->problems = Problem::query()->where('model_id', $this->selectedModel)->orderBy('name')->get();
        $this->selectedProblem = null;
        $this->resetTree();
    }

    public function updatedSelectedProblem()
    {
        $this->loadTree();
        $this->targetAttachQuestionId = null;
    }

    public function updatedSourceProblemId()
    {
        $this->sourceQuestionId = null;
    }

    public function resetTree()
    {
        $this->tree = [
            'problem' => null,
            'roots' => [],
            'nodes' => [],
            'count' => 0,
        ];
    }

    public function loadTree()
    {
        if (!$this->selectedProblem) {
            $this->resetTree();
            return;
        }

        $problem = Problem::find($this->selectedProblem);
        $questions = Question::query()
            ->where('problem_id', $this->selectedProblem)
            ->get(['id', 'question_text', 'yes_question_id', 'no_question_id']);

        $referenced = [];
        foreach ($questions as $q) {
            if ($q->yes_question_id) {
                $referenced[$q->yes_question_id] = true;
            }
            if ($q->no_question_id) {
                $referenced[$q->no_question_id] = true;
            }
        }

        $nodes = [];
        foreach ($questions as $q) {
            $nodes[$q->id] = [
                'id' => $q->id,
                'text' => $q->question_text,
                'yes' => $q->yes_question_id,
                'no' => $q->no_question_id,
            ];
        }

        $roots = [];
        foreach ($questions as $q) {
            if (!isset($referenced[$q->id])) {
                $roots[] = $q->id;
            }
        }
        sort($roots);

        $this->tree = [
            'problem' => $problem ? ['id' => $problem->id, 'name' => $problem->name] : null,
            'roots' => $roots,
            'nodes' => $nodes,
            'count' => count($nodes),
        ];
    }

    public function cloneFlow(QuestionFlowCloner $cloner)
    {
        $this->validate([
            'sourceProblemId' => ['required', 'exists:problems,id'],
            'sourceQuestionId' => ['required', 'exists:questions,id'],
            'selectedProblem' => ['required', 'exists:problems,id'],
            'attachMode' => ['required', 'in:yes,no,root'],
            'targetAttachQuestionId' => ['nullable', 'exists:questions,id'],
        ]);

        $sourceQuestion = Question::query()
            ->whereKey($this->sourceQuestionId)
            ->where('problem_id', $this->sourceProblemId)
            ->first();

        if (!$sourceQuestion) {
            session()->flash('error', 'Source question not found for the selected source problem.');
            return;
        }

        if ($this->attachMode !== 'root' && !$this->targetAttachQuestionId) {
            session()->flash('error', 'Choose a target attach question.');
            return;
        }

        try {
            $result = $cloner->cloneAndAttach(
                (int) $this->sourceQuestionId,
                (int) $this->selectedProblem,
                (string) $this->attachMode,
                $this->targetAttachQuestionId ? (int) $this->targetAttachQuestionId : null,
            );
        } catch (\Throwable $e) {
            session()->flash('error', $e->getMessage());
            return;
        }

        session()->flash('message', "Cloned {$result['cloned_count']} questions successfully.");
        $this->loadTree();
    }

    public function getSourceQuestionsProperty()
    {
        if (!$this->sourceProblemId) {
            return collect();
        }

        return Question::query()
            ->where('problem_id', $this->sourceProblemId)
            ->orderBy('id')
            ->get(['id', 'question_text']);
    }

    public function getTargetQuestionsProperty()
    {
        if (!$this->selectedProblem) {
            return collect();
        }

        return Question::query()
            ->where('problem_id', $this->selectedProblem)
            ->orderBy('id')
            ->get(['id', 'question_text']);
    }

    public function getFilteredSourceQuestionsProperty()
    {
        $q = trim((string) $this->cloneSearch);
        $items = $this->sourceQuestions;
        if ($q === '') {
            return $items;
        }

        return $items->filter(function ($x) use ($q) {
            $hay = strtolower($x->id.' '.$x->question_text);
            return str_contains($hay, strtolower($q));
        })->values();
    }

    public function getFilteredTargetQuestionsProperty()
    {
        $q = trim((string) $this->cloneSearch);
        $items = $this->targetQuestions;
        if ($q === '') {
            return $items;
        }

        return $items->filter(function ($x) use ($q) {
            $hay = strtolower($x->id.' '.$x->question_text);
            return str_contains($hay, strtolower($q));
        })->values();
    }

    public function render()
    {
        $allProblems = Problem::query()->orderBy('name')->get(['id', 'name']);

        return view('livewire.admin.solution.tree-explorer', [
            'allProblems' => $allProblems,
        ]);
    }
}

