<div class="mx-auto bg-slate-50 rounded-lg">
    <div class="flex justify-between items-center p-3 bg-slate-100 rounded-t-lg">
        <h2 class="text-lg font-bold text-gray-700">Support & Diagnosis</h2>

        {{-- Reset Button --}}
        <x-danger-button wire:click="resetSelection"
            class="bg-red-500 hover:bg-red-600 text-white px-4 py-1 rounded">
            Reset
        </x-danger-button>
    </div>

    <div class="grid sm:grid-cols-2 md:grid-cols-4 p-3 gap-3">
        {{-- Device Selection --}}
        <div class="bg-gray-100 p-3 rounded-lg border border-gray-200">
            <label class="block font-medium text-gray-700">Select Device:</label>
            <select wire:model.change="selectedDevice"
                class="w-full mt-1 p-2 border rounded focus:ring-2 focus:ring-blue-400  {{$selectedDevice ? " bg-slate-300 cursor-not-allowed" : ''}}" {{$selectedDevice ? "disabled" : ""}}>
                <option value="">Choose Device</option>
                @foreach($devices as $device)
                    <option value="{{ $device->id }}">{{ $device->name }}</option>
                @endforeach
            </select>
        </div>

        {{-- Brand Selection --}}
        @if($brands)
        <div class="bg-gray-100 p-3 rounded-lg border border-gray-200">
            <label class="block font-medium text-gray-700">Select Brand:</label>
            <select wire:model.change="selectedBrand"
                class="w-full mt-1 p-2 border rounded focus:ring-2 focus:ring-blue-400  {{$selectedBrand ? " bg-slate-300 cursor-not-allowed" : ''}}" {{$selectedBrand ? "disabled" : ""}}>
                <option value="">Choose Brand</option>
                @foreach($brands as $brand)
                    <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                @endforeach
            </select>
        </div>
        @endif

        {{-- Model Selection --}}
        @if($models)
        <div class="bg-gray-100 p-3 rounded-lg border border-gray-200">
            <label class="block font-medium text-gray-700">Select Model:</label>
            <select wire:model.change="selectedModel"
                class="w-full mt-1 p-2 border rounded focus:ring-2 focus:ring-blue-400 {{$selectedModel ? " bg-slate-300 cursor-not-allowed" : ''}}" {{$selectedModel ? "disabled" : ""}}>
                <option value="">Choose Model</option>
                @foreach($models as $model)
                    <option value="{{ $model->id }}">{{ $model->name }}</option>
                @endforeach
            </select>
        </div>
        @endif

        {{-- Problem Selection --}}
        @if($problems)
        <div class="bg-gray-100 p-3 rounded-lg border border-gray-200">
            <label class="block font-medium text-gray-700">Select Problem:</label>
            <select wire:model.change="selectedProblem"
                class="w-full mt-1 p-2 border rounded focus:ring-2 focus:ring-blue-400 {{$selectedProblem ? " bg-slate-300 cursor-not-allowed" : ''}}" {{$selectedProblem ? "disabled" : ""}}>
                <option value="">Choose Problem</option>
                @foreach($problems as $problem)
                    <option value="{{ $problem->id }}">{{ $problem->name }}</option>
                @endforeach
            </select>
        </div>
        @endif
    </div>

    {{-- Question Box --}}
    @if($currentQuestion)
    <div class="mt-6 bg-white flex flex-col items-center  p-4 rounded-lg shadow">
        <p class="text-3xl mb-4 font-semibold text-gray-700">{{ $currentQuestion->question_text }}</p>
        <div class="mt-3 space-x-4">
            <x-primary-button class="text-3xl" wire:click="answerQuestion('yes')">Yes</x-primary-button>
            <x-danger-button class="text-3xl" wire:click="answerQuestion('no')">No</x-danger-button>
        </div>
    </div>
    @endif
</div>
