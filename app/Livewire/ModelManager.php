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
    }

    public function editModel($id)
    {
        $model = ModelTable::findOrFail($id);
        $this->editingId = $id;
        $this->name = $model->name;
        $this->brand_id = $model->brand_id;
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
    }

    public function deleteModel($id)
    {
        ModelTable::destroy($id);
        session()->flash('message', 'Model deleted successfully!');
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
