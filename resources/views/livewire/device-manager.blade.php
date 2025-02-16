<div class=" mx-auto p-6 bg-white rounded-xl">
    <h2 class="text-3xl font-semibold mb-4 text-gray-800">Manage Devices</h2>

    @if (session()->has('message'))
        <div class="mb-4 p-3 bg-green-100 text-green-700 border border-green-400 rounded-lg">
            {{ session('message') }}
        </div>
    @endif

    {{-- Create or Edit Form --}}
    <form wire:submit.prevent="{{ $editingId ? 'updateDevice' : 'createDevice' }}" class="mb-6">
        <div class="flex items-center gap-3">
            <input type="text" wire:model="name"
                class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="Enter device name">
            <button type="submit"
                class="px-5 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-300">
                {{ $editingId ? 'Update' : 'Create' }}
            </button>
            @if ($editingId)
                <button type="button" wire:click="resetInput"
                    class="px-5 py-2 bg-gray-400 text-white rounded-lg hover:bg-gray-500 transition duration-300">
                    Cancel
                </button>
            @endif
        </div>
        @error('name')
            <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
        @enderror
    </form>

    {{-- Device List --}}
    <div class="overflow-hidden rounded-lg border border-gray-200">
        <table class="w-full border-collapse">
            <thead>
                <tr class="bg-gray-100 text-gray-700">
                    <th class="px-6 py-3 text-left font-semibold">ID</th>
                    <th class="px-6 py-3 text-left font-semibold">Name</th>
                    <th class="px-6 py-3 text-center font-semibold">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($devices as $device)
                    <tr class="border-b hover:bg-gray-50 transition">
                        <td class="px-6 py-3">{{ $device->id }}</td>
                        <td class="px-6 py-3 font-medium text-gray-800">{{ $device->name }}</td>
                        <td class="px-6 py-3 text-center space-x-2">
                            <button wire:click="editDevice({{ $device->id }})"
                                class="px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition duration-300">Edit</button>
                            @if ($device->brands->isEmpty()) {{-- Check if device has no brands --}}
                                <button wire:click="deleteDevice({{ $device->id }})"
                                    class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition duration-300">Delete</button>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="mt-6 flex justify-center">
        {{ $devices->links() }}
    </div>
</div>
