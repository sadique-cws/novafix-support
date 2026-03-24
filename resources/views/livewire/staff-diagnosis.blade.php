<div class="mx-auto">
    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="flex items-center justify-between px-5 py-4 bg-gray-50 rounded-t-2xl border-b border-gray-200">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">Support & Diagnosis</h2>
                <p class="text-sm text-gray-600">Select device details and answer questions to reach the fault area.</p>
            </div>

            <button type="button" wire:click="resetSelection"
                class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">
                Reset
            </button>
        </div>

        <div class="p-5 grid gap-4 md:grid-cols-4">
            <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                <label class="block text-sm font-semibold text-gray-800">Select Device</label>
                <select wire:model.change="selectedDevice"
                    class="mt-2 w-full rounded-lg border border-gray-300 p-3 text-sm focus:ring-2 focus:ring-blue-500 {{$selectedDevice ? " bg-slate-200 cursor-not-allowed" : ''}}" {{$selectedDevice ? "disabled" : ""}}>
                    <option value="">Choose Device</option>
                    @foreach($devices as $device)
                        <option value="{{ $device->id }}">{{ $device->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                <label class="block text-sm font-semibold text-gray-800">Select Brand</label>
                <select wire:model.change="selectedBrand"
                    class="mt-2 w-full rounded-lg border border-gray-300 p-3 text-sm focus:ring-2 focus:ring-blue-500 {{$selectedBrand ? " bg-slate-200 cursor-not-allowed" : ''}}" {{$selectedBrand ? "disabled" : ""}}>
                    <option value="">Choose Brand</option>
                    @foreach($brands as $brand)
                        <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                <label class="block text-sm font-semibold text-gray-800">Select Model</label>
                <select wire:model.change="selectedModel"
                    class="mt-2 w-full rounded-lg border border-gray-300 p-3 text-sm focus:ring-2 focus:ring-blue-500 {{$selectedModel ? " bg-slate-200 cursor-not-allowed" : ''}}" {{$selectedModel ? "disabled" : ""}}>
                    <option value="">Choose Model</option>
                    @foreach($models as $model)
                        <option value="{{ $model->id }}">{{ $model->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                <label class="block text-sm font-semibold text-gray-800">Select Problem</label>
                <select wire:model.change="selectedProblem"
                    class="mt-2 w-full rounded-lg border border-gray-300 p-3 text-sm focus:ring-2 focus:ring-blue-500 {{$selectedProblem ? " bg-slate-200 cursor-not-allowed" : ''}}" {{$selectedProblem ? "disabled" : ""}}>
                    <option value="">Choose Problem</option>
                    @foreach($problems as $problem)
                        <option value="{{ $problem->id }}">{{ $problem->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    {{-- Question Box --}}
    @if($currentQuestion)
        <div class="mt-10 rounded-2xl border border-gray-200 bg-white shadow-sm p-8">
            <div class="text-center">
                <div class="text-4xl font-bold text-gray-800 tracking-tight">
                    {{ $currentQuestion->question_text }}
                </div>

                <div class="mt-8 flex items-center justify-center gap-4">
                    <button type="button" wire:click="answerQuestion('yes')"
                        class="rounded-lg bg-green-700 px-8 py-3 text-lg font-semibold text-white hover:bg-green-800">
                        YES
                    </button>
                    <button type="button" wire:click="answerQuestion('no')"
                        class="rounded-lg bg-red-600 px-8 py-3 text-lg font-semibold text-white hover:bg-red-700">
                        NO
                    </button>
                </div>
            </div>
        </div>
    @elseif($selectedProblem && $isComplete)
        <div class="mt-10 rounded-2xl border border-green-200 bg-green-50 p-6">
            <h3 class="text-xl font-semibold text-green-900">Diagnosis complete</h3>
            <p class="mt-1 text-sm text-green-800">Flow ended. Use Reset to start again or choose another problem.</p>
        </div>
    @endif
</div>
