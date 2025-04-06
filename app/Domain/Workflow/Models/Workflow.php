<?php

namespace App\Domain\Workflow\Models;

use Illuminate\Database\Eloquent\Model;
use App\Domain\Workflow\Models\WorkflowStage;
use App\Domain\Workflow\Models\WorkflowTransition;
use App\Domain\Process\Models\Process;
use App\Models\User;

class Workflow extends Model
{
    protected $fillable = [
        'name',
        'description',
        'is_active',
        'created_by',
    ];

    public function stages()
    {
        return $this->hasMany(WorkflowStage::class)->orderBy('order');
    }

    public function transitions()
    {
        return $this->hasMany(WorkflowTransition::class);
    }

    public function processes()
    {
        return $this->hasMany(Process::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
