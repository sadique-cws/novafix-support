<?php


namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Device;

class DeviceManager extends Component
{
    use WithPagination;

    public $name;
    public $editingId = null;
    public $modalName = 'device-form';

    protected $rules = [
        'name' => 'required|string|max:255',
    ];

    public function createDevice()
    {
        $this->validate();
        Device::create(['name' => $this->name]);

        $this->resetInput();
        session()->flash('message', 'Device created successfully.');
        $this->dispatch('close-modal', name: $this->modalName);
    }

    public function startCreate()
    {
        $this->resetInput();
        $this->dispatch('open-modal', name: $this->modalName);
    }

    public function editDevice($id)
    {
        $device = Device::findOrFail($id);
        $this->editingId = $device->id;
        $this->name = $device->name;
        $this->dispatch('open-modal', name: $this->modalName);
    }

    public function updateDevice()
    {
        $this->validate();

        if ($this->editingId) {
            $device = Device::findOrFail($this->editingId);
            $device->update(['name' => $this->name]);

            $this->resetInput();
            session()->flash('message', 'Device updated successfully.');
            $this->dispatch('close-modal', name: $this->modalName);
        }
    }

    public function deleteDevice($id)
    {
        Device::findOrFail($id)->delete();
        session()->flash('message', 'Device deleted successfully.');
    }

    public function cancel()
    {
        $this->resetInput();
        $this->dispatch('close-modal', name: $this->modalName);
    }

    public function resetInput()
    {
        $this->name = '';
        $this->editingId = null;
    }

    public function render()
    {
        return view('livewire.device-manager', [
            'devices' => Device::latest()->paginate(5),
        ]);
    }
}
