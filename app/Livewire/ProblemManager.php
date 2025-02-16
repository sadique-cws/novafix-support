<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Problem;
use App\Models\Model as DeviceModel;

class ProblemManager extends Component
{
    use WithPagination;

    public $problemId, $name, $model_id;
    public $editing = false;
    public $models;

    protected $rules = [
        'name' => 'required|string|max:255',
        'model_id' => 'required|exists:models,id',
    ];

    public function mount()
    {
        $this->models = DeviceModel::all();
    }

    public function createProblem()
    {
        $this->validate();

        Problem::create([
            'name' => $this->name,
            'model_id' => $this->model_id,
        ]);

        session()->flash('message', 'Problem created successfully.');
        $this->resetForm();
    }

    public function editProblem($id)
    {
        $problem = Problem::findOrFail($id);
        $this->problemId = $problem->id;
        $this->name = $problem->name;
        $this->model_id = $problem->model_id;
        $this->editing = true;
    }

    public function updateProblem()
    {
        $this->validate();

        $problem = Problem::findOrFail($this->problemId);
        $problem->update([
            'name' => $this->name,
            'model_id' => $this->model_id,
        ]);

        session()->flash('message', 'Problem updated successfully.');
        $this->resetForm();
    }

    public function deleteProblem($id)
    {
        Problem::destroy($id);
        session()->flash('message', 'Problem deleted successfully.');
    }

    public function resetForm()
    {
        $this->problemId = null;
        $this->name = '';
        $this->model_id = null;
        $this->editing = false;
    }

    public function render()
    {
        return view('livewire.problem-manager', [
            'problems' => Problem::with('model')->paginate(10),
        ]);
    }
}
