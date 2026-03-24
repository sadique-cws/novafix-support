<div class="mx-auto bg-white rounded-xl shadow-sm border border-gray-200 p-6">
    <div>
        <h2 class="text-2xl font-semibold text-gray-900">Manage Models</h2>
        <p class="mt-1 text-sm text-gray-600">Models are linked to brands.</p>
    </div>

    @if (session()->has('message'))
        <div class="mt-4 rounded-lg border border-green-200 bg-green-50 p-3 text-green-800">
            {{ session('message') }}
        </div>
    @endif

    <div class="mt-6">
        <div class="flex items-center justify-end">
            <button type="button"
                wire:click="startCreate"
                class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                + Add Model
            </button>
        </div>

        <div class="mt-4">
            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr class="text-left text-xs font-semibold uppercase tracking-wider text-gray-600">
                                <th class="px-4 py-3">ID</th>
                                <th class="px-4 py-3">Model</th>
                                <th class="px-4 py-3">Brand</th>
                                <th class="px-4 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-sm text-gray-900">
                            @foreach ($models as $model)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3">{{ $model->id }}</td>
                                    <td class="px-4 py-3 font-medium">{{ $model->name }}</td>
                                    <td class="px-4 py-3 text-gray-700">{{ $model->brand->name }}</td>
                                    <td class="px-4 py-3">
                                        <div class="flex justify-end gap-2">
                                            <button wire:click="editModel({{ $model->id }})" class="rounded-md bg-yellow-500 px-3 py-1.5 text-xs font-semibold text-white hover:bg-yellow-600">
                                                Edit
                                            </button>
                                            @if ($model->problems->isEmpty())
                                                <button wire:click="deleteModel({{ $model->id }})" class="rounded-md bg-red-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-red-700">
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
                {{ $models->links() }}
            </div>
        </div>
    </div>

    <x-modal :name="$modalName" :title="$editingId ? 'Edit Model' : 'Add Model'" maxWidth="max-w-xl">
        <form wire:submit.prevent="{{ $editingId ? 'updateModel' : 'createModel' }}" class="space-y-4">
            <div>
                <label class="text-xs font-medium text-gray-700">Model name</label>
                <input type="text" wire:model="name"
                    class="mt-1 w-full rounded-lg border border-gray-300 p-2"
                    placeholder="e.g. DA0Z8MB8C0_Z8E">
                @error('name') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
            </div>

            <div>
                <label class="text-xs font-medium text-gray-700">Brand</label>
                <select wire:model="brand_id" class="mt-1 w-full rounded-lg border border-gray-300 p-2">
                    <option value="">Select a Brand</option>
                    @foreach($brands as $brand)
                        <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                    @endforeach
                </select>
                @error('brand_id') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
            </div>

            <div class="flex items-center justify-end gap-2">
                <button type="button" wire:click="cancel"
                    class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                    Cancel
                </button>
                <button type="submit"
                    class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                    {{ $editingId ? 'Update' : 'Create' }}
                </button>
            </div>
        </form>
    </x-modal>
</div>
