<div class="mt-6 p-6 bg-white shadow-lg rounded-lg">
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
        <div class="overflow-x-auto">
            <table class="w-full border border-gray-200 shadow-sm rounded-lg">
                <thead>
                    <tr class="bg-gray-100 text-gray-700 uppercase text-sm">
                        <th class="py-3 px-4 border">User</th>
                        <th class="py-3 px-4 border">Device</th>
                        <th class="py-3 px-4 border">Brand</th>
                        <th class="py-3 px-4 border">Model</th>
                        <th class="py-3 px-4 border">Problem</th>
                        <th class="py-3 px-4 border">Action</th>
                    </tr>
                </thead>
                <tbody class="text-gray-900">
                    @foreach($users as $user)
                        @foreach($user->answers->groupBy('problem_id') as $problemId => $answers)
                            <tr class="border-b hover:bg-gray-50 transition">
                                <td class="py-3 px-4 border">{{ $user->name }}</td>
                                <td class="py-3 px-4 border">{{ $answers->first()->device->name ?? '-' }}</td>
                                <td class="py-3 px-4 border">{{ $answers->first()->brand->name ?? '-' }}</td>
                                <td class="py-3 px-4 border">{{ $answers->first()->model->name ?? '-' }}</td>
                                <td class="py-3 px-4 border">{{ $answers->first()->problem->name ?? '-' }}</td>
                                <td class="py-3 px-4 border">
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
    @endif
</div>
