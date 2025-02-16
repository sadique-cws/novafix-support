<div class=" mx-auto p-4">
    <h2 class="text-xl font-bold mb-4">Manage Brands</h2>

    @if (session()->has('message'))
        <div class="bg-green-100 text-green-700 p-2 rounded mb-3">{{ session('message') }}</div>
    @endif

    <!-- Create or Edit Form -->
    <form wire:submit.prevent="{{ $editingId ? 'updateBrand' : 'createBrand' }}" class="mb-4 space-y-2">
        <input type="text" wire:model="name" placeholder="Enter brand name" class="border rounded px-3 py-2 w-full">
        @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

        <select wire:model="device_id" class="border rounded px-3 py-2 w-full">
            <option value="">Select Device</option>
            @foreach($devices as $device)
                <option value="{{ $device->id }}">{{ $device->name }}</option>
            @endforeach
        </select>
        @error('device_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

        <div class="flex space-x-2">
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">
                {{ $editingId ? 'Update' : 'Create' }}
            </button>
            @if($editingId)
                <button type="button" wire:click="resetInput" class="bg-gray-500 text-white px-4 py-2 rounded">Cancel</button>
            @endif
        </div>
    </form>

    <!-- Brand List -->
    <table class="w-full border-collapse border">
        <thead>
            <tr class="bg-gray-200">
                <th class="border p-2">ID</th>
                <th class="border p-2">Brand</th>
                <th class="border p-2">Device</th>
                <th class="border p-2">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($brands as $brand)
                <tr class="border">
                    <td class="border p-2">{{ $brand->id }}</td>
                    <td class="border p-2">{{ $brand->name }}</td>
                    <td class="border p-2">{{ $brand->device->name }}</td>
                    <td class="border p-2 space-x-2">
                        <button wire:click="editBrand({{ $brand->id }})" class="bg-yellow-500 text-white px-3 py-1 rounded">Edit</button>
                        <button wire:click="deleteBrand({{ $brand->id }})" class="bg-red-500 text-white px-3 py-1 rounded">Delete</button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $brands->links() }}
    </div>
</div>
