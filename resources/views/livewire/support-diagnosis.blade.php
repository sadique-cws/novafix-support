<div class="mx-auto">
    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="flex items-start justify-between gap-4 px-5 py-4 bg-gray-50 rounded-t-2xl border-b border-gray-200">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">Support & Diagnosis (Admin Builder)</h2>
                <p class="text-sm text-gray-600">Build and maintain the YES/NO question flow for a problem.</p>
            </div>

            <div class="flex items-center gap-2">
                <button type="button" wire:click="resetSelection"
                    class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800">
                    Reset
                </button>
            </div>
        </div>

        @if (session()->has('message'))
            <div class="m-5 rounded-lg border border-green-200 bg-green-50 p-3 text-green-800">
                {{ session('message') }}
            </div>
        @endif

        @if (session()->has('error'))
            <div class="m-5 rounded-lg border border-red-200 bg-red-50 p-3 text-red-800">
                {{ session('error') }}
            </div>
        @endif

        <div class="p-5 grid gap-4 md:grid-cols-4">
            <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                <label class="block text-sm font-semibold text-gray-800">Device</label>
                <select wire:model.change="selectedDevice"
                    class="mt-2 w-full rounded-lg border border-gray-300 p-3 text-sm focus:ring-2 focus:ring-blue-500 {{ $selectedDevice ? 'bg-slate-200 cursor-not-allowed' : '' }}"
                    {{ $selectedDevice ? 'disabled' : '' }}>
                    <option value="">Choose Device</option>
                    @foreach($devices as $device)
                        <option value="{{ $device->id }}">{{ $device->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                <label class="block text-sm font-semibold text-gray-800">Brand</label>
                <select wire:model.change="selectedBrand"
                    class="mt-2 w-full rounded-lg border border-gray-300 p-3 text-sm focus:ring-2 focus:ring-blue-500 {{ $selectedBrand ? 'bg-slate-200 cursor-not-allowed' : '' }}"
                    {{ $selectedBrand ? 'disabled' : '' }}>
                    <option value="">Choose Brand</option>
                    @foreach($brands as $brand)
                        <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                <label class="block text-sm font-semibold text-gray-800">Model</label>
                <select wire:model.change="selectedModel"
                    class="mt-2 w-full rounded-lg border border-gray-300 p-3 text-sm focus:ring-2 focus:ring-blue-500 {{ $selectedModel ? 'bg-slate-200 cursor-not-allowed' : '' }}"
                    {{ $selectedModel ? 'disabled' : '' }}>
                    <option value="">Choose Model</option>
                    @foreach($models as $model)
                        <option value="{{ $model->id }}">{{ $model->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                <label class="block text-sm font-semibold text-gray-800">Problem</label>
                <select wire:model.change="selectedProblem"
                    class="mt-2 w-full rounded-lg border border-gray-300 p-3 text-sm focus:ring-2 focus:ring-blue-500 {{ $selectedProblem ? 'bg-slate-200 cursor-not-allowed' : '' }}"
                    {{ $selectedProblem ? 'disabled' : '' }}>
                    <option value="">Choose Problem</option>
                    @foreach($problems as $problem)
                        <option value="{{ $problem->id }}">{{ $problem->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    {{-- Builder workspace --}}
    @if(isset($selectedProblem))
        <div class="mt-6 grid gap-4 lg:grid-cols-3">
            <div class="lg:col-span-2 rounded-2xl border border-gray-200 bg-white shadow-sm p-6">
                <div class="flex items-center justify-between gap-3">
                    <div class="text-sm font-semibold text-gray-800">Current Question</div>
                    @if($currentQuestion)
                        <div class="flex items-center gap-2">
                            @if(\App\Models\Question::where('yes_question_id', $currentQuestion->id)->orWhere('no_question_id', $currentQuestion->id)->exists())
                                <button type="button" wire:click="previousQuestion"
                                    class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50">
                                    Previous
                                </button>
                            @endif
                            <button type="button" wire:click="editQuestion({{ $currentQuestion->id }})"
                                class="rounded-lg bg-indigo-600 px-3 py-2 text-xs font-semibold text-white hover:bg-indigo-700">
                                Edit text
                            </button>
                        </div>
                    @endif
                </div>

                @if(!$currentQuestion)
                    <div class="mt-4 rounded-xl border border-dashed border-gray-300 bg-gray-50 p-6 text-sm text-gray-700">
                        <div class="font-semibold text-gray-900">No root question yet</div>
                        <div class="mt-1 text-gray-600">Create the first question for this problem to start the flow.</div>
                        <button type="button" wire:click="openFirstQuestionModal"
                            class="mt-4 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                            Create first question
                        </button>
                    </div>
                @else
                    <div class="mt-6">
                        @if($editingQuestionId === $currentQuestion->id)
                            <div class="space-y-3">
                                <label class="text-xs font-medium text-gray-700">Edit question</label>
                                <textarea wire:model="editingQuestionText" rows="3"
                                    class="w-full rounded-lg border border-gray-300 p-3 text-sm focus:ring-2 focus:ring-blue-500"></textarea>
                                @error('editingQuestionText')
                                    <div class="text-xs text-red-600">{{ $message }}</div>
                                @enderror
                                <div class="flex items-center justify-end gap-2">
                                    <button type="button" wire:click="cancelEdit"
                                        class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                                        Cancel
                                    </button>
                                    <button type="button" wire:click="updateQuestion"
                                        class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800">
                                        Save
                                    </button>
                                </div>
                            </div>
                        @else
                            <div class="rounded-2xl border border-gray-200 bg-white p-8 text-center">
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

                                @if(isset($newQuestionAnswer))
                                    <div class="mt-8 rounded-xl border border-amber-200 bg-amber-50 p-4 text-left">
                                        <div class="text-sm font-semibold text-amber-900">Flow ends here</div>
                                        <div class="mt-1 text-sm text-amber-800">
                                            Add a new question to the <span class="font-semibold">{{ strtoupper($newQuestionAnswer) }}</span> branch.
                                        </div>
                                        <button type="button" wire:click="openNewQuestionModal"
                                            class="mt-3 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                                            Add question
                                        </button>
                                        <button type="button" wire:click="cancelNewQuestion"
                                            class="mt-3 ml-2 rounded-lg border border-amber-300 bg-white px-4 py-2 text-sm font-semibold text-amber-900 hover:bg-amber-100">
                                            Clear
                                        </button>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm p-6">
                <div class="text-sm font-semibold text-gray-800">Clone / Reuse Flow</div>
                <p class="mt-1 text-sm text-gray-600">Copy a complete sub-flow and attach it to this problem.</p>

                <div class="mt-4 space-y-3">
                    <div>
                        <label class="text-xs font-medium text-gray-700">Source problem</label>
                        <select wire:model.change="sourceProblemId"
                            class="mt-1 w-full rounded-lg border border-gray-300 p-2 text-sm">
                            <option value="">Choose source problem</option>
                            @foreach($problemsList as $p)
                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="text-xs font-medium text-gray-700">Start question</label>
                        <select wire:model.change="sourceQuestionId"
                            class="mt-1 w-full rounded-lg border border-gray-300 p-2 text-sm"
                            @disabled(!$sourceProblemId)>
                            <option value="">Choose source question</option>
                            @foreach($sourceQuestions as $q)
                                <option value="{{ $q->id }}">#{{ $q->id }} — {{ \Illuminate\Support\Str::limit($q->question_text, 70) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="text-xs font-medium text-gray-700">Attach mode</label>
                        <select wire:model.change="targetAttachBranch"
                            class="mt-1 w-full rounded-lg border border-gray-300 p-2 text-sm">
                            <option value="yes">Attach to YES branch</option>
                            <option value="no">Attach to NO branch</option>
                            <option value="root">Set as ROOT (only if empty)</option>
                        </select>
                    </div>

                    @if($targetAttachBranch !== 'root')
                        <div>
                            <label class="text-xs font-medium text-gray-700">Attach to question</label>
                            <select wire:model.change="targetAttachToQuestionId"
                                class="mt-1 w-full rounded-lg border border-gray-300 p-2 text-sm">
                                <option value="">Choose target question</option>
                                @foreach($targetQuestions as $tq)
                                    <option value="{{ $tq->id }}">#{{ $tq->id }} — {{ \Illuminate\Support\Str::limit($tq->question_text, 70) }}</option>
                                @endforeach
                            </select>
                            <div class="mt-1 text-xs text-gray-500">Pick where the cloned flow should continue.</div>
                        </div>
                    @else
                        <div class="rounded-lg border border-gray-200 bg-gray-50 p-3 text-xs text-gray-600">
                            Root cloning is allowed only when this problem has no questions yet.
                        </div>
                    @endif

                    <button type="button" wire:click="cloneFlow"
                        class="w-full rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700 disabled:opacity-50"
                        @disabled(!$sourceProblemId || !$sourceQuestionId)>
                        Clone & Attach
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Modals --}}
    <x-modal :name="$modalFirstQuestion" title="Create first question" maxWidth="max-w-2xl">
        <div class="text-sm text-gray-600">This becomes the root of the flow for the selected problem.</div>
        <div class="mt-4">
            <label class="text-xs font-medium text-gray-700">Question text</label>
            <textarea wire:model="newQuestionText" rows="3"
                class="mt-1 w-full rounded-lg border border-gray-300 p-3 text-sm focus:ring-2 focus:ring-blue-500"
                placeholder="e.g. Is adapter voltage present?"></textarea>
            @error('newQuestionText')
                <div class="mt-1 text-xs text-red-600">{{ $message }}</div>
            @enderror
        </div>

        <div class="mt-5 flex items-center justify-end gap-2">
            <button type="button" wire:click="cancelFirstQuestion"
                class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                Cancel
            </button>
            <button type="button" wire:click="createFirstQuestion"
                class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                Create
            </button>
        </div>
    </x-modal>

    <x-modal :name="$modalNewQuestion" title="Add a new question" maxWidth="max-w-2xl">
        <div class="text-sm text-gray-600">
            Adds a question to the <span class="font-semibold">{{ $newQuestionAnswer ? strtoupper($newQuestionAnswer) : '' }}</span> branch.
        </div>

        @if($currentQuestion)
            <div class="mt-3 rounded-lg border border-gray-200 bg-gray-50 p-3 text-sm text-gray-700">
                <span class="font-semibold">Parent:</span> {{ $currentQuestion->question_text }}
            </div>
        @endif

        <div class="mt-4">
            <label class="text-xs font-medium text-gray-700">Question text</label>
            <textarea wire:model="newQuestionText" rows="3"
                class="mt-1 w-full rounded-lg border border-gray-300 p-3 text-sm focus:ring-2 focus:ring-blue-500"
                placeholder="Enter the next diagnostic question..."></textarea>
            @error('newQuestionText')
                <div class="mt-1 text-xs text-red-600">{{ $message }}</div>
            @enderror
        </div>

        <div class="mt-5 flex items-center justify-end gap-2">
            <button type="button" wire:click="cancelNewQuestion"
                class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                Cancel
            </button>
            <button type="button" wire:click="createQuestion"
                class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                Save
            </button>
        </div>
    </x-modal>
</div>

