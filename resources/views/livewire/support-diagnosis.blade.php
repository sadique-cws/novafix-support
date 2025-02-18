<div class="mx-auto bg-slate-100 rounded-lg">
    <div class="flex justify-between items-center p-3 bg-slate-300">
        <h2 class="text-lg font-bold text-gray-700">Support & Diagnosis</h2>

        {{-- Reset Button --}}
        <button wire:click="resetSelection"
            class="bg-red-500 hover:bg-red-600 text-white px-4 py-1 rounded">
            Reset
        </button>
    </div>

    <div class="grid sm:grid-cols-2 md:grid-cols-4  p-3 gap-3">

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
   {{-- Question Box with Modern Styling --}}
@if($currentQuestion && !isset($newQuestionAnswer))
<div class="mt-6 bg-gradient-to-r from-blue-50 to-blue-100 p-6 rounded-xl shadow-lg border border-blue-200">
    @if($editingQuestionId === $currentQuestion->id)
        <div class="flex flex-col space-y-3">
            <input type="text" wire:model="editingQuestionText"
                class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none text-lg">
            @error('editingQuestionText')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
            <div class="flex space-x-3">
                <button wire:click="updateQuestion"
                    class="px-5 py-2 bg-green-500 hover:bg-green-600 text-white font-semibold rounded-lg shadow-md transition-all">
                    ✅ Save
                </button>
                <button wire:click="cancelEdit"
                    class="px-5 py-2 bg-gray-400 hover:bg-gray-500 text-white font-semibold rounded-lg shadow-md transition-all">
                    ❌ Cancel
                </button>
            </div>
        </div>
    @else
        <p class="text-2xl font-semibold text-gray-800 text-center mt-3 leading-snug">
            {{ $currentQuestion->question_text }}
        </p>

        <div class="mt-5 flex flex-col items-center space-y-4">
            <div class="flex space-x-4">
                <button wire:click="answerQuestion('yes')"
                    class="px-6 py-3 bg-green-500 hover:bg-green-600 text-white font-medium rounded-lg shadow-md transition-all">
                    ✅ Yes
                </button>
                <button wire:click="answerQuestion('no')"
                    class="px-6 py-3 bg-red-500 hover:bg-red-600 text-white font-medium rounded-lg shadow-md transition-all">
                    ❌ No
                </button>
            </div>

            @if(\App\Models\Question::where('yes_question_id', $currentQuestion->id)->orWhere('no_question_id', $currentQuestion->id)->exists())
            <button wire:click="previousQuestion"
                class="mt-3 px-4 py-2 bg-gray-300 hover:bg-gray-400 text-black font-medium rounded-lg shadow-md transition-all">
                ⬅️ Previous Question
            </button>
            @endif

            <button wire:click="editQuestion({{ $currentQuestion->id }})"
                class="mt-3 text-blue-600 hover:text-blue-700 font-medium transition-all">
                ✏️ Edit this Question
            </button>
        </div>
    @endif
</div>
@endif


    {{-- New Question Form --}}
    @if(isset($newQuestionAnswer))
    <div class="mt-6 p-6 bg-gradient-to-r from-gray-50 to-gray-100 rounded-xl shadow-lg border border-gray-300">
        <h3 class="text-2xl font-semibold text-gray-800 mb-4">
            📝 Create a New Question for "{{ ucfirst($newQuestionAnswer) }}"
        </h3>

        @if($currentQuestion)
        <p class="text-sm text-gray-600 bg-gray-200 p-2 rounded-md">
            <strong class="text-gray-700">Previous Question:</strong> {{ $currentQuestion->question_text }}
        </p>
        @endif

        <div class="mt-4">
            <label class="block font-medium text-gray-700 text-lg mb-1">Enter the Question:</label>
            <input type="text" wire:model="newQuestionText"
                class="w-full p-3 border rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 outline-none text-lg">
            @error('newQuestionText')
                <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span>
            @enderror
        </div>

        <button wire:click="createQuestion"
            class="mt-5 w-full px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-md transition-all">
            💾 Save {{ ucfirst($newQuestionAnswer) }} Question
        </button>
    </div>
    @endif


    {{-- First Question Form --}}
    @if(!$currentQuestion && isset($selectedProblem))
    <div class="mt-6 p-6 bg-gradient-to-r from-gray-50 to-gray-100 rounded-xl shadow-lg border border-gray-300">
        <h3 class="text-2xl font-semibold text-gray-800 mb-4">
            📝 Create the First Question for this Problem
        </h3>

        <div class="mt-4">
            <label class="block text-lg font-medium text-gray-700 mb-2">Enter the First Question:</label>
            <input type="text" wire:model="newQuestionText"
                class="w-full p-3 border rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 outline-none text-lg">
            @error('newQuestionText')
                <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span>
            @enderror
        </div>

        <button wire:click="createFirstQuestion"
            class="mt-5 w-full px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-md transition-all">
            💾 Save First Question
        </button>
    </div>
    @endif


</div>
