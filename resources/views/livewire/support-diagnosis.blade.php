<div class="mx-auto bg-slate-100 rounded-lg">
    <div class="flex justify-between items-center p-3 bg-slate-300">
        <h2 class="text-lg font-bold text-gray-700">Support & Diagnosis</h2>

        {{-- Reset Button --}}
        <button wire:click="resetSelection"
            class="bg-red-500 hover:bg-red-600 text-white px-4 py-1 rounded">
            Reset
        </button>
    </div>

    @if (session()->has('message'))
        <div class="m-3 p-3 bg-green-50 text-green-800 border border-green-200 rounded-lg">
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="m-3 p-3 bg-red-50 text-red-800 border border-red-200 rounded-lg">
            {{ session('error') }}
        </div>
    @endif

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

    {{-- Clone / Reuse Flow --}}
    @if(isset($selectedProblem))
    <div class="mt-6 p-6 bg-white rounded-xl shadow-lg border border-gray-200">
        <h3 class="text-xl font-semibold text-gray-800">Clone / Reuse Question Flow</h3>
        <p class="text-sm text-gray-600 mt-1">
            Copy a complete sub-flow starting from any question, and attach it into this Problem’s flow (creates new copied questions).
        </p>

        <div class="grid md:grid-cols-2 gap-4 mt-4">
            <div class="p-4 bg-gray-50 border border-gray-200 rounded-lg">
                <h4 class="font-semibold text-gray-700 mb-3">Source</h4>
                <label class="block text-sm font-medium text-gray-700">Source Problem</label>
                <select wire:model.change="sourceProblemId"
                    class="w-full mt-1 p-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="">Choose source problem</option>
                    @foreach($problemsList as $p)
                        <option value="{{ $p->id }}">{{ $p->name }}</option>
                    @endforeach
                </select>

                <label class="block text-sm font-medium text-gray-700 mt-3">Start Question (root of the copied sub-flow)</label>
                <select wire:model.change="sourceQuestionId"
                    class="w-full mt-1 p-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
                    @disabled(!$sourceProblemId)>
                    <option value="">Choose source question</option>
                    @foreach($sourceQuestions as $q)
                        <option value="{{ $q->id }}">#{{ $q->id }} — {{ \Illuminate\Support\Str::limit($q->question_text, 80) }}</option>
                    @endforeach
                </select>
            </div>

            <div class="p-4 bg-gray-50 border border-gray-200 rounded-lg">
                <h4 class="font-semibold text-gray-700 mb-3">Target (Current Problem)</h4>

                <label class="block text-sm font-medium text-gray-700">Attach Mode</label>
                <select wire:model.change="targetAttachBranch"
                    class="w-full mt-1 p-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="yes">Attach to YES branch</option>
                    <option value="no">Attach to NO branch</option>
                    <option value="root">Set as ROOT (only if no questions yet)</option>
                </select>

                @if($targetAttachBranch !== 'root')
                    <label class="block text-sm font-medium text-gray-700 mt-3">Attach to Question</label>
                    <select wire:model.change="targetAttachToQuestionId"
                        class="w-full mt-1 p-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">Choose target question</option>
                        @foreach($targetQuestions as $tq)
                            <option value="{{ $tq->id }}">#{{ $tq->id }} — {{ \Illuminate\Support\Str::limit($tq->question_text, 80) }}</option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-500 mt-2">Tip: pick the question where you want to continue the flow.</p>
                @else
                    <p class="text-xs text-gray-500 mt-2">Root cloning is only allowed when this Problem has no questions yet.</p>
                @endif
            </div>
        </div>

        <div class="mt-4 flex justify-end">
            <button wire:click="cloneFlow"
                class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg shadow-md transition-all disabled:opacity-50"
                @disabled(!$sourceProblemId || !$sourceQuestionId)>
                Clone & Attach
            </button>
        </div>
    </div>
    @endif


</div>
