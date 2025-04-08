<?php

namespace App\Domain\Webhook\Models;

use Illuminate\Database\Eloquent\Model;
use App\Domain\Process\Models\Process;
use App\Domain\Workflow\Models\Workflow;

class Webhook extends Model
{
    protected $fillable = [
        'name',
        'description',
        'url',
        'secret',
        'events', // Array JSON com eventos como 'process.created', 'process.stage_changed'
        'workflow_id', // Opcional, para limitar a workflows específicos
        'is_active',
        'headers', // Cabeçalhos personalizados em JSON
        'retry_count',
        'max_retries'
    ];

    protected $casts = [
        'events' => 'array',
        'headers' => 'array',
        'is_active' => 'boolean',
    ];

    public function workflow()
    {
        return $this->belongsTo(Workflow::class);
    }

    public function logs()
    {
        return $this->hasMany(WebhookLog::class);
    }
}