<?php

namespace App\Domain\Process\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Domain\Process\Models\Process;
use App\Domain\Workflow\Models\WorkflowStage;
use App\Models\User;

class ProcessHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'process_id',
        'from_stage_id',
        'to_stage_id',
        'action',
        'comments',
        'performed_by',
    ];

    public function process()
    {
        return $this->belongsTo(Process::class);
    }

    public function fromStage()
    {
        return $this->belongsTo(WorkflowStage::class, 'from_stage_id');
    }

    public function toStage()
    {
        return $this->belongsTo(WorkflowStage::class, 'to_stage_id');
    }

    public function performer()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
