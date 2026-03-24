<div class="mx-auto bg-white rounded-xl shadow-sm border border-gray-200 p-6">
    <div class="flex items-start justify-between gap-4">
        <div>
            <h2 class="text-2xl font-semibold text-gray-900">Manage Brands</h2>
            <p class="mt-1 text-sm text-gray-600">Create, edit, and remove brands (linked to devices).</p>
        </div>
    </div>

    @if (session()->has('message'))
        <div class="mt-4 rounded-lg border border-green-200 bg-green-50 p-3 text-green-800">
            {{ session('message') }}
        </div>
    @endif

    <div class="mt-6 grid gap-4 lg:grid-cols-3">
        <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
            <h3 class="text-sm font-semibold text-gray-800">{{ $editingId ? 'Edit Brand' : 'Create Brand' }}</h3>

            <form wire:submit.prevent="{{ $editingId ? 'updateBrand' : 'createBrand' }}" class="mt-4 space-y-3">
                <div>
                    <label class="text-xs font-medium text-gray-700">Brand name</label>
                    <input type="text" wire:model="name" placeholder="e.g. Acer"
                        class="mt-1 w-full rounded-lg border border-gray-300 p-2">
                    @error('name') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="text-xs font-medium text-gray-700">Device</label>
                    <select wire:model="device_id" class="mt-1 w-full rounded-lg border border-gray-300 p-2">
                        <option value="">Select Device</option>
                        @foreach($devices as $device)
                            <option value="{{ $device->id }}">{{ $device->name }}</option>
                        @endforeach
                    </select>
                    @error('device_id') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                </div>

                <div class="flex items-center gap-2 pt-1">
                    <button type="submit"
                        class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                        {{ $editingId ? 'Update' : 'Create' }}
                    </button>
                    @if($editingId)
                        <button type="button" wire:click="resetInput"
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
                                <th class="px-4 py-3">Brand</th>
                                <th class="px-4 py-3">Device</th>
                                <th class="px-4 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-sm text-gray-900">
                            @foreach ($brands as $brand)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3">{{ $brand->id }}</td>
                                    <td class="px-4 py-3 font-medium">{{ $brand->name }}</td>
                                    <td class="px-4 py-3 text-gray-700">{{ $brand->device->name }}</td>
                                    <td class="px-4 py-3">
                                        <div class="flex justify-end gap-2">
                                            <button wire:click="editBrand({{ $brand->id }})"
                                                class="rounded-md bg-yellow-500 px-3 py-1.5 text-xs font-semibold text-white hover:bg-yellow-600">
                                                Edit
                                            </button>
                                            @if ($brand->models->isEmpty())
                                                <button wire:click="deleteBrand({{ $brand->id }})"
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
                {{ $brands->links() }}
            </div>
        </div>
    </div>
</div>
