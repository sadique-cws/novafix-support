<div class="mx-auto bg-white rounded-xl shadow-sm border border-gray-200 p-6">
    <div>
        <h2 class="text-2xl font-semibold text-gray-900">Manage Problems</h2>
        <p class="mt-1 text-sm text-gray-600">Problems are linked to models and hold the diagnosis question flows.</p>
    </div>

    @if (session()->has('message'))
        <div class="mt-4 rounded-lg border border-green-200 bg-green-50 p-3 text-green-800">
            {{ session('message') }}
        </div>
    @endif

    <div class="mt-6 grid gap-4 lg:grid-cols-3">
        <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
            <h3 class="text-sm font-semibold text-gray-800">{{ $editing ? 'Edit Problem' : 'Create Problem' }}</h3>

            <form wire:submit.prevent="{{ $editing ? 'updateProblem' : 'createProblem' }}" class="mt-4 space-y-3">
                <div>
                    <label class="text-xs font-medium text-gray-700">Problem name</label>
                    <input type="text" wire:model="name"
                        class="mt-1 w-full rounded-lg border border-gray-300 p-2"
                        placeholder="e.g. Power, Display issue, Dead">
                    @error('name') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="text-xs font-medium text-gray-700">Model</label>
                    <select wire:model="model_id" class="mt-1 w-full rounded-lg border border-gray-300 p-2">
                        <option value="">Select Model</option>
                        @foreach($models as $model)
                            <option value="{{ $model->id }}">{{ $model->name }}</option>
                        @endforeach
                    </select>
                    @error('model_id') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                </div>

                <div class="flex items-center gap-2 pt-1">
                    <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                        {{ $editing ? 'Update' : 'Create' }}
                    </button>
                    @if($editing)
                        <button type="button" wire:click="resetForm"
                            class="rounded-lg bg-gray-700 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800">
                            Cancel
                        </button>
                    @endif
                </div>
            </form>
        </div>

        <div class="lg:col-span-2">
            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr class="text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                                <th class="px-4 py-3">ID</th>
                                <th class="px-4 py-3">Problem</th>
                                <th class="px-4 py-3">Model</th>
                                <th class="px-4 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-sm text-gray-900">
                            @foreach($problems as $problem)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3">{{ $problem->id }}</td>
                                    <td class="px-4 py-3 font-medium">{{ $problem->name }}</td>
                                    <td class="px-4 py-3 text-gray-700">{{ $problem->model->name }}</td>
                                    <td class="px-4 py-3">
                                        <div class="flex justify-end gap-2">
                                            <button wire:click="editProblem({{ $problem->id }})"
                                                class="rounded-md bg-yellow-500 px-3 py-1.5 text-xs font-semibold text-white hover:bg-yellow-600">
                                                Edit
                                            </button>
                                            @if($problem->questions->isEmpty())
                                                <button wire:click="deleteProblem({{ $problem->id }})"
                                                    class="rounded-md bg-red-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-red-700">
                                                    Delete
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-4">
                {{ $problems->links() }}
            </div>
        </div>
    </div>
</div>
