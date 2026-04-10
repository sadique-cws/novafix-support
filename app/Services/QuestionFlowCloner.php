<?php

namespace App\Services;

use App\Models\Question;
use Illuminate\Support\Facades\DB;

class QuestionFlowCloner
{
    /**
     * Clone a subtree (starting at $sourceQuestionId) into $targetProblemId and attach it.
     *
     * Note: we copy image_url but DO NOT copy image_file_id to avoid accidental
     * deletion of the original ImageKit asset when editing/removing the cloned question.
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
                throw new \RuntimeException('Target problem already has questions. Attach to YES/NO instead of ROOT.');
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
                throw new \RuntimeException('Target attach question not found for this problem.');
            }

            if ($attachMode === 'yes') {
                if ($targetAttachQuestion->yes_question_id) {
                    throw new \RuntimeException('Target question already has a YES branch.');
                }
                $targetAttachQuestion->yes_question_id = $clonedRootId;
            } else {
                if ($targetAttachQuestion->no_question_id) {
                    throw new \RuntimeException('Target question already has a NO branch.');
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
            'description' => $source->description,
            'image_url' => $source->image_url,
            'image_file_id' => null,
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

