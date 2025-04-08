<?php

namespace App\Domain\Webhook\Models;

use Illuminate\Database\Eloquent\Model;

class WebhookLog extends Model
{
    protected $fillable = [
        'webhook_id',
        'event_name',
        'payload',
        'status_code',
        'response',
        'success',
        'attempt'
    ];

    protected $casts = [
        'payload' => 'array',
        'success' => 'boolean',
    ];

    public function webhook()
    {
        return $this->belongsTo(Webhook::class);
    }
}
