<div class="max-w-4xl mx-auto p-6 bg-slate-100 rounded-2xl">
    <h2 class="text-2xl font-semibold mb-4 text-gray-700">Manage Devices</h2>

    @if (session()->has('message'))
        <div class="mb-4 p-3 bg-green-100 text-green-700 rounded-md">
            {{ session('message') }}
        </div>
    @endif

    {{-- Create or Edit Form --}}
    <form wire:submit.prevent="{{ $editingId ? 'updateDevice' : 'createDevice' }}" class="mb-6">
        <div class="flex space-x-3">
            <input type="text" wire:model="name" class="w-full p-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400" placeholder="Enter device name">
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                {{ $editingId ? 'Update' : 'Create' }}
            </button>
            @if($editingId)
                <button type="button" wire:click="resetInput" class="px-4 py-2 bg-gray-400 text-white rounded-md hover:bg-gray-500">
                    Cancel
                </button>
            @endif
        </div>
        @error('name')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
        @enderror
    </form>

    {{-- Device List --}}
    <div class="overflow-x-auto rounded-lg">
        <table class="w-full table-auto border-collapse">
            <thead>
                <tr class="">
                    <th class="px-4 py-2 text-left text-gray-700">ID</th>
                    <th class="px-4 py-2 text-left text-gray-700">Name</th>
                    <th class="px-4 py-2 text-center text-gray-700">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($devices as $device)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-4 py-2">{{ $device->id }}</td>
                        <td class="px-4 py-2">{{ $device->name }}</td>
                        <td class="px-4 py-2 text-center space-x-2">
                            <button wire:click="editDevice({{ $device->id }})" class="px-3 py-1 bg-yellow-500 text-white rounded-md hover:bg-yellow-600">Edit</button>
                            <button wire:click="deleteDevice({{ $device->id }})" class="px-3 py-1 bg-red-500 text-white rounded-md hover:bg-red-600">Delete</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="mt-4">
        {{ $devices->links() }}
    </div>
</div>
