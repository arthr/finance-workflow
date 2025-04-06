<?php

namespace App\Domain\Workflow\Models;

use Illuminate\Database\Eloquent\Model;
use App\Domain\Workflow\Models\Workflow;
use App\Domain\Workflow\Models\WorkflowStage;

class WorkflowTransition extends Model
{
    protected $fillable = [
        'workflow_id',
        'from_stage_id',
        'to_stage_id',
        'condition',
        'trigger_type',
    ];

    protected $casts = [
        'condition' => 'array',
    ];

    public function workflow()
    {
        return $this->belongsTo(Workflow::class);
    }

    public function fromStage()
    {
        return $this->belongsTo(WorkflowStage::class, 'from_stage_id');
    }

    public function toStage()
    {
        return $this->belongsTo(WorkflowStage::class, 'to_stage_id');
    }
}
