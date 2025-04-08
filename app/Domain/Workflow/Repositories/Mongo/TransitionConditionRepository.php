<?php

namespace App\Domain\Workflow\Repositories\Mongo;

use App\Domain\Workflow\MongoModels\TransitionCondition;
use Illuminate\Support\Facades\Auth;

class TransitionConditionRepository
{
    /**
     * Buscar condição de uma transição pelo ID
     */
    public function findByTransitionId(int $transitionId): ?TransitionCondition
    {
        return TransitionCondition::where('transition_id', $transitionId)->first();
    }

    /**
     * Salvar ou atualizar condição de transição
     */
    public function saveCondition(int $transitionId, array $condition): TransitionCondition
    {
        $transitionCondition = $this->findByTransitionId($transitionId);

        if ($transitionCondition) {
            $transitionCondition->update([
                'condition' => $condition,
                'updated_by' => Auth::id()
            ]);
        } else {
            $transitionCondition = TransitionCondition::create([
                'transition_id' => $transitionId,
                'condition' => $condition,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id()
            ]);
        }

        return $transitionCondition;
    }

    /**
     * Excluir condição de transição
     */
    public function deleteCondition(int $transitionId): bool
    {
        $transitionCondition = $this->findByTransitionId($transitionId);

        if ($transitionCondition) {
            return $transitionCondition->delete();
        }

        return false;
    }
}
