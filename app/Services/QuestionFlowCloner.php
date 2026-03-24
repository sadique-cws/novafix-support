<?php

namespace App\Services;

use App\Models\Question;
use Illuminate\Support\Facades\DB;

class QuestionFlowCloner
{
    /**
     * Clone a subtree (starting at $sourceQuestionId) into $targetProblemId and attach it.
     *
     * @return array{cloned_root_id:int, cloned_count:int}
     */
    public function cloneAndAttach(
        int $sourceQuestionId,
        int $targetProblemId,
        string $attachMode,
        ?int $targetAttachQuestionId = null
    ): array {
        if (!in_array($attachMode, ['yes', 'no', 'root'], true)) {
            throw new \InvalidArgumentException('Invalid attach mode.');
        }

        return DB::transaction(function () use ($sourceQuestionId, $targetProblemId, $attachMode, $targetAttachQuestionId) {
            if ($attachMode === 'root' && Question::query()->where('problem_id', $targetProblemId)->exists()) {
                throw new \RuntimeException('Target problem already has questions. Attach to a Yes/No branch instead of Root.');
            }

            $idMap = [];
            $visiting = [];
            $clonedRootId = $this->cloneQuestionSubtree($sourceQuestionId, $targetProblemId, $idMap, $visiting);

            if ($attachMode === 'root') {
                return [
                    'cloned_root_id' => $clonedRootId,
                    'cloned_count' => count($idMap),
                ];
            }

            if (!$targetAttachQuestionId) {
                throw new \RuntimeException('Target attach question is required.');
            }

            $targetAttachQuestion = Question::query()
                ->whereKey($targetAttachQuestionId)
                ->where('problem_id', $targetProblemId)
                ->first();

            if (!$targetAttachQuestion) {
                throw new \RuntimeException('Target attach question not found for the selected target problem.');
            }

            if ($attachMode === 'yes') {
                if ($targetAttachQuestion->yes_question_id) {
                    throw new \RuntimeException('Target question already has a YES branch. Clear it before attaching a cloned flow.');
                }
                $targetAttachQuestion->yes_question_id = $clonedRootId;
            } else {
                if ($targetAttachQuestion->no_question_id) {
                    throw new \RuntimeException('Target question already has a NO branch. Clear it before attaching a cloned flow.');
                }
                $targetAttachQuestion->no_question_id = $clonedRootId;
            }

            $targetAttachQuestion->save();

            return [
                'cloned_root_id' => $clonedRootId,
                'cloned_count' => count($idMap),
            ];
        });
    }

    private function cloneQuestionSubtree(int $sourceQuestionId, int $targetProblemId, array &$idMap, array &$visiting): int
    {
        if (isset($idMap[$sourceQuestionId])) {
            return $idMap[$sourceQuestionId];
        }

        if (isset($visiting[$sourceQuestionId])) {
            throw new \RuntimeException('Cycle detected in the source flow. Cannot clone.');
        }

        $visiting[$sourceQuestionId] = true;

        $source = Question::find($sourceQuestionId);
        if (!$source) {
            throw new \RuntimeException("Source question {$sourceQuestionId} not found.");
        }

        $copy = Question::create([
            'problem_id' => $targetProblemId,
            'question_text' => $source->question_text,
            'yes_question_id' => null,
            'no_question_id' => null,
        ]);

        $idMap[$sourceQuestionId] = $copy->id;

        if ($source->yes_question_id) {
            $copy->yes_question_id = $this->cloneQuestionSubtree((int) $source->yes_question_id, $targetProblemId, $idMap, $visiting);
        }
        if ($source->no_question_id) {
            $copy->no_question_id = $this->cloneQuestionSubtree((int) $source->no_question_id, $targetProblemId, $idMap, $visiting);
        }

        $copy->save();

        unset($visiting[$sourceQuestionId]);

        return $copy->id;
    }
}

