<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Device;
use App\Models\Model as DeviceModel;
use App\Models\Problem;
use App\Models\Question;
use App\Services\QuestionFlowCloner;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminDiagnosisController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Diagnosis', [
            'devices' => Device::query()->orderBy('name')->get(['id', 'name']),
            'brands' => Brand::query()->orderBy('name')->get(['id', 'name', 'device_id']),
            'models' => DeviceModel::query()->orderBy('name')->get(['id', 'name', 'brand_id']),
            'problems' => Problem::query()->orderBy('name')->get(['id', 'name', 'model_id']),
        ]);
    }

    public function tree(Problem $problem)
    {
        $questions = Question::query()
            ->where('problem_id', $problem->id)
            ->get(['id', 'question_text', 'yes_question_id', 'no_question_id']);

        $referenced = [];
        foreach ($questions as $q) {
            if ($q->yes_question_id) {
                $referenced[$q->yes_question_id] = true;
            }
            if ($q->no_question_id) {
                $referenced[$q->no_question_id] = true;
            }
        }

        $nodes = [];
        foreach ($questions as $q) {
            $nodes[$q->id] = [
                'id' => $q->id,
                'text' => $q->question_text,
                'yes' => $q->yes_question_id,
                'no' => $q->no_question_id,
            ];
        }

        $roots = [];
        foreach ($questions as $q) {
            if (!isset($referenced[$q->id])) {
                $roots[] = $q->id;
            }
        }
        sort($roots);

        return response()->json([
            'problem' => ['id' => $problem->id, 'name' => $problem->name],
            'roots' => $roots,
            'nodes' => $nodes,
            'count' => count($nodes),
        ]);
    }

    public function questions(Problem $problem)
    {
        $questions = Question::query()
            ->where('problem_id', $problem->id)
            ->orderBy('id')
            ->get(['id', 'question_text']);

        return response()->json([
            'questions' => $questions->map(fn ($q) => [
                'id' => $q->id,
                'text' => $q->question_text,
            ])->values(),
        ]);
    }

    public function clone(Request $request, QuestionFlowCloner $cloner)
    {
        $validated = $request->validate([
            'source_problem_id' => ['required', 'exists:problems,id'],
            'source_question_id' => ['required', 'exists:questions,id'],
            'target_problem_id' => ['required', 'exists:problems,id'],
            'attach_mode' => ['required', 'in:yes,no,root'],
            'target_attach_question_id' => ['nullable', 'exists:questions,id'],
        ]);

        $sourceQuestion = Question::query()
            ->whereKey($validated['source_question_id'])
            ->where('problem_id', $validated['source_problem_id'])
            ->first();

        if (!$sourceQuestion) {
            return back()->with('error', 'Source question not found for the selected source problem.');
        }

        try {
            $result = $cloner->cloneAndAttach(
                (int) $validated['source_question_id'],
                (int) $validated['target_problem_id'],
                (string) $validated['attach_mode'],
                $validated['target_attach_question_id'] ? (int) $validated['target_attach_question_id'] : null,
            );
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('message', "Cloned {$result['cloned_count']} questions successfully.");
    }

    public function updateQuestion(Request $request, Question $question)
    {
        $validated = $request->validate([
            'question_text' => ['required', 'string', 'min:3'],
        ]);

        $question->update([
            'question_text' => $validated['question_text'],
        ]);

        return back()->with('message', 'Question updated.');
    }

    public function createRootQuestion(Request $request)
    {
        $validated = $request->validate([
            'problem_id' => ['required', 'exists:problems,id'],
            'question_text' => ['required', 'string', 'min:3'],
        ]);

        $hasAny = Question::query()->where('problem_id', $validated['problem_id'])->exists();
        if ($hasAny) {
            return back()->with('error', 'This problem already has questions. Create a YES/NO child instead of a new root.');
        }

        Question::create([
            'problem_id' => $validated['problem_id'],
            'question_text' => $validated['question_text'],
        ]);

        return back()->with('message', 'Root question created.');
    }

    public function createBranchQuestion(Request $request)
    {
        $validated = $request->validate([
            'parent_question_id' => ['required', 'exists:questions,id'],
            'attach_mode' => ['required', 'in:yes,no'],
            'question_text' => ['required', 'string', 'min:3'],
        ]);

        $parent = Question::find($validated['parent_question_id']);
        if (!$parent) {
            return back()->with('error', 'Parent question not found.');
        }

        if ($validated['attach_mode'] === 'yes' && $parent->yes_question_id) {
            return back()->with('error', 'This parent already has a YES branch.');
        }
        if ($validated['attach_mode'] === 'no' && $parent->no_question_id) {
            return back()->with('error', 'This parent already has a NO branch.');
        }

        $child = Question::create([
            'problem_id' => $parent->problem_id,
            'question_text' => $validated['question_text'],
        ]);

        if ($validated['attach_mode'] === 'yes') {
            $parent->yes_question_id = $child->id;
        } else {
            $parent->no_question_id = $child->id;
        }
        $parent->save();

        return back()->with('message', 'Child question created and attached.');
    }
}
