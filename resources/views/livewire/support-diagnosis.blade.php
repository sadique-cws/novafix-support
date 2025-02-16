<div class="mx-auto bg-slate-100 rounded-lg">
    <div class="flex justify-between items-center p-3 bg-slate-300">
        <h2 class="text-lg font-bold text-gray-700">Support & Diagnosis</h2>

        {{-- Reset Button --}}
        <button wire:click="resetSelection"
            class="bg-red-500 hover:bg-red-600 text-white px-4 py-1 rounded">
            Reset
        </button>
    </div>

    <div class="grid grid-cols-4 p-3 gap-3">

        {{-- Device Selection --}}
        <div>
            <label class="block font-medium text-gray-700">Select Device:</label>
            <select wire:model.change="selectedDevice"
                class="w-full mt-1 p-2 border rounded focus:ring-2 focus:ring-blue-400 {{$selectedDevice ? " bg-slate-200 cursor-not-allowed" : ''}}" {{ $selectedDevice ? 'disabled' : '' }}>
                <option value="">Choose Device</option>
                @foreach($devices as $device)
                    <option value="{{ $device->id }}">{{ $device->name }}</option>
                @endforeach
            </select>
        </div>



        {{-- Brand Selection --}}
        @if($brands)
        <div>
            <label class="block font-medium text-gray-700">Select Brand:</label>
            <select wire:model.change="selectedBrand"
                class="w-full mt-1 p-2 border rounded focus:ring-2 focus:ring-blue-400 {{$selectedBrand ? " bg-slate-200 cursor-not-allowed" : ''}}" {{ $selectedBrand ? 'disabled bg-slate-200' : '' }}>
                <option value="">Choose Brand</option>
                @foreach($brands as $brand)
                    <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                @endforeach
            </select>
        </div>
        @endif

        {{-- Model Selection --}}
        @if($models)
        <div>
            <label class="block font-medium text-gray-700">Select Model:</label>
            <select wire:model.change="selectedModel"
                class="w-full mt-1 p-2 border rounded focus:ring-2 focus:ring-blue-400 {{$selectedModel ? " bg-slate-200 cursor-not-allowed" : ''}}" {{ $selectedModel ? 'disabled bg-slate-200' : '' }}>
                <option value="">Choose Model</option>
                @foreach($models as $model)
                    <option value="{{ $model->id }}">{{ $model->name }}</option>
                @endforeach
            </select>
        </div>
        @endif

        {{-- Problem Selection --}}
        @if($problems)
        <div>
            <label class="block font-medium text-gray-700">Select Problem:</label>
            <select wire:model.change="selectedProblem"
                class="w-full mt-1 p-2 border rounded focus:ring-2 focus:ring-blue-400 {{$selectedProblem ? " bg-slate-200 cursor-not-allowed" : ''}}" {{ $selectedProblem ? 'disabled bg-slate-200' : '' }}>
                <option value="">Choose Problem</option>
                @foreach($problems as $problem)
                    <option value="{{ $problem->id }}">{{ $problem->name }}</option>
                @endforeach
            </select>
        </div>
        @endif
    </div>

    {{-- Question Box with Inline Editing --}}
    @if($currentQuestion && !isset($newQuestionAnswer))
    <div class="mt-6 bg-blue-100 p-4 rounded-lg shadow">
        @if($editingQuestionId === $currentQuestion->id)
            <input type="text" wire:model="editingQuestionText"
                class="w-full p-2 border rounded focus:ring-2 focus:ring-blue-400">
            @error('editingQuestionText')
                <span class="text-red-500 text-sm capitalize">{{ $message }}</span>
            @enderror
            <div class="mt-3 space-x-2">
                <button wire:click="updateQuestion"
                    class="px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded shadow">
                    Save
                </button>
                <button wire:click="cancelEdit"
                    class="px-4 py-2 bg-gray-400 hover:bg-gray-500 text-white rounded shadow">
                    Cancel
                </button>
            </div>
        @else
            <p class="text-3xl font-semibold text-gray-700 capitalize text-center mt-3">{{ $currentQuestion->question_text }}</p>
            <div class="mt-3 space-x-4 flex justify-between items-center flex-col">
                <div class="flex-1 mt-5">
                    <button wire:click="answerQuestion('yes')"
                    class="px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded shadow">
                    Yes
                </button>
                <button wire:click="answerQuestion('no')"
                    class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded shadow">
                    No
                </button>
                </div>
                <button wire:click="editQuestion({{ $currentQuestion->id }})"
                    class="px-3 py-1 text-black rounded self-end">
                    Edit this Question
                </button>
            </div>
        @endif
    </div>
    @endif

    {{-- New Question Form --}}
    @if(isset($newQuestionAnswer))
    <div class="mt-6 p-6 bg-gray-100 rounded-lg shadow-md">
        <h3 class="text-xl font-semibold text-gray-700 mb-3">
            Create a New Question for "{{ ucfirst($newQuestionAnswer) }}"
        </h3>

        @if($currentQuestion)
        <p class="text-sm text-gray-600"><strong>Previous Question:</strong> {{ $currentQuestion->question_text }}</p>
        @endif

        <div class="mt-3">
            <label class="block font-medium text-gray-700">Enter the Question:</label>
            <input type="text" wire:model="newQuestionText"
                class="w-full mt-1 p-2 border rounded focus:ring-2 focus:ring-blue-400">
            @error('newQuestionText')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <button wire:click="createQuestion"
            class="mt-4 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded shadow">
            Save {{ ucfirst($newQuestionAnswer) }} Question
        </button>
    </div>
    @endif
</div>
