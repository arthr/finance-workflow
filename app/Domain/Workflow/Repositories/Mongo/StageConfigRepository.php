<?php

namespace App\Domain\Workflow\Repositories\Mongo;

use App\Domain\Workflow\MongoModels\StageConfig;
use Illuminate\Support\Facades\Auth;

class StageConfigRepository
{
    /**
     * Buscar configuração de um estágio pelo ID
     */
    public function findByStageId(int $stageId): ?StageConfig
    {
        return StageConfig::where('stage_id', $stageId)->first();
    }

    /**
     * Salvar ou atualizar configuração de estágio
     */
    public function saveConfig(int $stageId, array $config): StageConfig
    {
        $stageConfig = $this->findByStageId($stageId);

        if ($stageConfig) {
            $stageConfig->update([
                'config' => $config,
                'updated_by' => Auth::id()
            ]);
        } else {
            $stageConfig = StageConfig::create([
                'stage_id' => $stageId,
                'config' => $config,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id()
            ]);
        }

        return $stageConfig;
    }

    /**
     * Excluir configuração de estágio
     */
    public function deleteConfig(int $stageId): bool
    {
        $stageConfig = $this->findByStageId($stageId);

        if ($stageConfig) {
            return $stageConfig->delete();
        }

        return false;
    }
}
