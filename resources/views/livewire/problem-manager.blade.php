<div class="max-w-4xl mx-auto p-6 bg-white shadow rounded">
    <h2 class="text-lg font-semibold mb-4">{{ $editing ? 'Edit Problem' : 'Add Problem' }}</h2>

    @if (session()->has('message'))
        <div class="p-3 mb-3 text-green-700 bg-green-100 rounded">{{ session('message') }}</div>
    @endif

    <form wire:submit.prevent="{{ $editing ? 'updateProblem' : 'createProblem' }}" class="space-y-4">
        <div>
            <label class="block text-sm font-medium text-gray-700">Problem Name</label>
            <input type="text" wire:model="name"
                class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring focus:ring-blue-300">
            @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Select Model</label>
            <select wire:model="model_id"
                class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring focus:ring-blue-300">
                <option value="">Select Model</option>
                @foreach($models as $model)
                    <option value="{{ $model->id }}">{{ $model->name }}</option>
                @endforeach
            </select>
            @error('model_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="flex space-x-2">
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                {{ $editing ? 'Update' : 'Create' }}
            </button>
            @if($editing)
                <button type="button" wire:click="resetForm" class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">
                    Cancel
                </button>
            @endif
        </div>
    </form>

    <h2 class="mt-6 text-lg font-semibold">Problems List</h2>

    <table class="w-full mt-4 border-collapse border border-gray-300">
        <thead class="bg-gray-100">
            <tr>
                <th class="border border-gray-300 px-4 py-2">ID</th>
                <th class="border border-gray-300 px-4 py-2">Name</th>
                <th class="border border-gray-300 px-4 py-2">Model</th>
                <th class="border border-gray-300 px-4 py-2">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($problems as $problem)
                <tr>
                    <td class="border border-gray-300 px-4 py-2">{{ $problem->id }}</td>
                    <td class="border border-gray-300 px-4 py-2">{{ $problem->name }}</td>
                    <td class="border border-gray-300 px-4 py-2">{{ $problem->model->name }}</td>
                    <td class="border border-gray-300 px-4 py-2 space-x-2">
                        <button wire:click="editProblem({{ $problem->id }})"
                            class="px-3 py-1 bg-yellow-500 text-white rounded hover:bg-yellow-600">Edit</button>
                        <button wire:click="deleteProblem({{ $problem->id }})"
                            class="px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600">Delete</button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="mt-4">{{ $problems->links() }}</div>
</div>
