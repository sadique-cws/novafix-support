<?php

namespace App\Livewire;

use App\Models\User;
use App\Models\UserAnswer;
use Livewire\Component;

class AnswerList extends Component
{
    public $selectedUser;
    public $selectedProblem;
    public $userAnswers = [];
    public $currentAnswerIndex = 0;
    public $selectedProblemId = null; // Filter for a specific problem

    public function viewAnswers($userId, $problemId)
    {
        $this->selectedUser = User::find($userId);
        $this->selectedProblem = $problemId;
        $this->userAnswers = UserAnswer::where('user_id', $userId)
            ->where('problem_id', $problemId)
            ->with('question')
            ->get();
        $this->currentAnswerIndex = 0;
    }

    public function nextAnswer()
    {
        if ($this->currentAnswerIndex < count($this->userAnswers) - 1) {
            $this->currentAnswerIndex++;
        }
    }

    public function prevAnswer()
    {
        if ($this->currentAnswerIndex > 0) {
            $this->currentAnswerIndex--;
        }
    }

    public function render()
{
    // Fetch all problems for the dropdown filter
    $problems = \App\Models\Problem::all();

    // Fetch users grouped by problem
    $users = User::whereHas('answers', function ($query) {
        if ($this->selectedProblemId) {
            $query->where('problem_id', $this->selectedProblemId);
        }
    })
    ->with(['answers.problem', 'answers.device', 'answers.brand', 'answers.model'])
    ->get();

    return view('livewire.answer-list', compact('users', 'problems'));
}

}
