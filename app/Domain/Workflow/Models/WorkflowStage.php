<?php

namespace App\Domain\Workflow\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Domain\Workflow\Models\Workflow;
use App\Domain\Workflow\Models\WorkflowTransition;

class WorkflowStage extends Model
{
    use HasFactory;

    protected $fillable = [
        'workflow_id',
        'name',
        'description',
        'order',
        'type',
        'config',
    ];

    protected $casts = [
        'config' => 'array',
    ];

    public function workflow()
    {
        return $this->belongsTo(Workflow::class);
    }

    public function incomingTransitions()
    {
        return $this->hasMany(WorkflowTransition::class, 'to_stage_id');
    }

    public function outgoingTransitions()
    {
        return $this->hasMany(WorkflowTransition::class, 'from_stage_id');
    }
}
