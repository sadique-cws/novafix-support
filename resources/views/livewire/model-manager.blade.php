<div class=" mx-auto p-6 bg-white shadow-md rounded-lg">
    <h2 class="text-2xl font-bold mb-4">Manage Models</h2>

    @if (session()->has('message'))
        <div class="bg-green-500 text-white p-2 rounded mb-3">{{ session('message') }}</div>
    @endif

    {{-- Create or Edit Form --}}
    <form wire:submit.prevent="{{ $editingId ? 'updateModel' : 'createModel' }}" class="mb-4">
        <div class="mb-2">
            <label class="block text-gray-700">Model Name:</label>
            <input type="text" wire:model="name" class="w-full p-2 border rounded">
            @error('name') <span class="text-red-500">{{ $message }}</span> @enderror
        </div>

        <div class="mb-2">
            <label class="block text-gray-700">Select Brand:</label>
            <select wire:model="brand_id" class="w-full p-2 border rounded">
                <option value="">Select a Brand</option>
                @foreach($brands as $brand)
                    <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                @endforeach
            </select>
            @error('brand_id') <span class="text-red-500">{{ $message }}</span> @enderror
        </div>

        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">
            {{ $editingId ? 'Update' : 'Create' }}
        </button>
        @if($editingId)
            <button type="button" wire:click="resetInput" class="ml-2 bg-gray-500 text-white px-4 py-2 rounded">Cancel</button>
        @endif
    </form>

    {{-- Model List --}}
    <table class="w-full border-collapse border border-gray-300">
        <thead>
            <tr class="bg-gray-100">
                <th class="border p-2">ID</th>
                <th class="border p-2">Model Name</th>
                <th class="border p-2">Brand</th>
                <th class="border p-2">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($models as $model)
                <tr class="border-b">
                    <td class="border p-2">{{ $model->id }}</td>
                    <td class="border p-2">{{ $model->name }}</td>
                    <td class="border p-2">{{ $model->brand->name }}</td>
                    <td class="border p-2">
                        <button wire:click="editModel({{ $model->id }})" class="bg-yellow-500 text-white px-2 py-1 rounded">Edit</button>
                        <button wire:click="deleteModel({{ $model->id }})" class="bg-red-500 text-white px-2 py-1 rounded ml-2">Delete</button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Pagination --}}
    <div class="mt-4">
        {{ $models->links() }}
    </div>
</div>
