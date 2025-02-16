<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Brand;
use App\Models\Device;

class BrandManager extends Component
{
    use WithPagination;

    public $name, $device_id, $editingId;
    public $devices;

    protected $rules = [
        'name' => 'required|string|max:255',
        'device_id' => 'required|exists:devices,id',
    ];

    public function mount()
    {
        $this->devices = Device::all();
    }

    public function createBrand()
    {
        $this->validate();

        Brand::create([
            'name' => $this->name,
            'device_id' => $this->device_id,
        ]);

        session()->flash('message', 'Brand created successfully.');
        $this->resetInput();
    }

    public function editBrand($id)
    {
        $brand = Brand::findOrFail($id);
        $this->editingId = $brand->id;
        $this->name = $brand->name;
        $this->device_id = $brand->device_id;
    }

    public function updateBrand()
    {
        $this->validate();

        $brand = Brand::findOrFail($this->editingId);
        $brand->update([
            'name' => $this->name,
            'device_id' => $this->device_id,
        ]);

        session()->flash('message', 'Brand updated successfully.');
        $this->resetInput();
    }

    public function deleteBrand($id)
    {
        Brand::destroy($id);
        session()->flash('message', 'Brand deleted successfully.');
    }

    public function resetInput()
    {
        $this->name = '';
        $this->device_id = null;
        $this->editingId = null;
    }

    public function render()
    {
        return view('livewire.brand-manager', [
            'brands' => Brand::with('device')->paginate(10),
        ]);
    }
}
