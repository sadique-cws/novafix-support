<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Model as ModelTable;
use App\Models\Brand;

class ModelManager extends Component
{
    use WithPagination;

    public $name, $brand_id, $editingId;
    public $brands;
    public $modalName = 'model-form';

    public function mount()
    {
        $this->brands = Brand::all();
    }

    protected $rules = [
        'name' => 'required|string|max:255',
        'brand_id' => 'required|exists:brands,id',
    ];

    public function createModel()
    {
        $this->validate();
        ModelTable::create([
            'name' => $this->name,
            'brand_id' => $this->brand_id,
        ]);

        session()->flash('message', 'Model added successfully!');
        $this->resetInput();
        $this->dispatch('close-modal', name: $this->modalName);
    }

    public function startCreate()
    {
        $this->resetInput();
        $this->dispatch('open-modal', name: $this->modalName);
    }

    public function editModel($id)
    {
        $model = ModelTable::findOrFail($id);
        $this->editingId = $id;
        $this->name = $model->name;
        $this->brand_id = $model->brand_id;
        $this->dispatch('open-modal', name: $this->modalName);
    }

    public function updateModel()
    {
        $this->validate();
        $model = ModelTable::findOrFail($this->editingId);
        $model->update([
            'name' => $this->name,
            'brand_id' => $this->brand_id,
        ]);

        session()->flash('message', 'Model updated successfully!');
        $this->resetInput();
        $this->dispatch('close-modal', name: $this->modalName);
    }

    public function deleteModel($id)
    {
        ModelTable::destroy($id);
        session()->flash('message', 'Model deleted successfully!');
    }

    public function cancel()
    {
        $this->resetInput();
        $this->dispatch('close-modal', name: $this->modalName);
    }

    public function resetInput()
    {
        $this->name = '';
        $this->brand_id = '';
        $this->editingId = null;
    }

    public function render()
    {
        return view('livewire.model-manager', [
            'models' => ModelTable::with('brand')->paginate(5)
        ]);
    }
}
