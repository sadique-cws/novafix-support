<div class="mx-auto bg-white rounded-xl shadow-sm border border-gray-200 p-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-2xl font-semibold text-gray-900">Manage Answers</h2>
            <p class="mt-1 text-sm text-gray-600">Review engineer responses for a specific diagnosis flow.</p>
        </div>

        @if(isset($problems))
            <div class="flex items-center gap-2">
                <label class="text-xs font-medium text-gray-700">Filter problem</label>
                <select wire:model.change="selectedProblemId" class="rounded-lg border border-gray-300 p-2 text-sm">
                    <option value="">All</option>
                    @foreach($problems as $problem)
                        <option value="{{ $problem->id }}">{{ $problem->name }}</option>
                    @endforeach
                </select>
            </div>
        @endif
    </div>

    @if($selectedUser && count($userAnswers) > 0)
        <h3 class="text-xl font-semibold text-gray-800 mb-4">
            Answers for {{ $selectedUser->name }}
        </h3>

        <div class="p-4 bg-gray-50 border border-gray-200 rounded-lg shadow-sm">
            <p class="text-lg font-medium text-gray-700">
                <span class="font-semibold text-gray-900">Question:</span>
                {{ $userAnswers[$currentAnswerIndex]->question->question_text }}
            </p>
            <p class="text-lg font-medium text-gray-700 mt-2">
                <span class="font-semibold @if ($userAnswers[$currentAnswerIndex]->selected_answer == "yes")
                        text-green-600
                @else
                        text-red-600
                @endif">Answer:
                {{ $userAnswers[$currentAnswerIndex]->selected_answer }}
            </span>
            </p>
        </div>

        <div class="flex justify-between mt-6">
            <button wire:click="prevAnswer" class="px-5 py-2 bg-gray-600 text-white font-medium rounded-lg shadow transition hover:bg-gray-700 disabled:opacity-50"
                @if($currentAnswerIndex == 0) disabled @endif>
                &larr; Previous
            </button>
            <button wire:click="nextAnswer" class="px-5 py-2 bg-blue-600 text-white font-medium rounded-lg shadow transition hover:bg-blue-700 disabled:opacity-50"
                @if($currentAnswerIndex == count($userAnswers) - 1) disabled @endif>
                Next &rarr;
            </button>
        </div>
    @else
        <div class="mt-6 overflow-hidden rounded-xl border border-gray-200">
            <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr class="bg-gray-50 text-gray-600 uppercase text-xs font-semibold tracking-wider">
                        <th class="py-3 px-4 text-left">User</th>
                        <th class="py-3 px-4 text-left">Device</th>
                        <th class="py-3 px-4 text-left">Brand</th>
                        <th class="py-3 px-4 text-left">Model</th>
                        <th class="py-3 px-4 text-left">Problem</th>
                        <th class="py-3 px-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm text-gray-900">
                    @foreach($users as $user)
                        @foreach($user->answers->groupBy('problem_id') as $problemId => $answers)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="py-3 px-4">{{ $user->name }}</td>
                                <td class="py-3 px-4 text-gray-700">{{ $answers->first()->device->name ?? '-' }}</td>
                                <td class="py-3 px-4 text-gray-700">{{ $answers->first()->brand->name ?? '-' }}</td>
                                <td class="py-3 px-4 text-gray-700">{{ $answers->first()->model->name ?? '-' }}</td>
                                <td class="py-3 px-4 font-medium">{{ $answers->first()->problem->name ?? '-' }}</td>
                                <td class="py-3 px-4 text-right">
                                    <button wire:click="viewAnswers({{ $user->id }}, {{ $problemId }})"
                                        class="px-3 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg shadow transition hover:bg-blue-700">
                                        View Answers
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
            </div>
        </div>
    @endif
</div>
