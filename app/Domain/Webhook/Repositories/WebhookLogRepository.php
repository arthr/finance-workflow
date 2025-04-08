<?php

namespace App\Domain\Webhook\Repositories;

use App\Domain\Webhook\Models\WebhookLog;
use Illuminate\Pagination\LengthAwarePaginator;

class WebhookLogRepository
{
    public function findById(int $id): ?WebhookLog
    {
        return WebhookLog::find($id);
    }

    public function findByWebhookId(int $webhookId, $event = null, $status = null): LengthAwarePaginator
    {
        $query = WebhookLog::where('webhook_id', $webhookId);
        
        if ($event) {
            $query->where('event', $event);
        }
        
        if ($status === 'success') {
            $query->where('status_code', '>=', 200)->where('status_code', '<', 300);
        } elseif ($status === 'error') {
            $query->where(function($q) {
                $q->where('status_code', '<', 200)
                  ->orWhere('status_code', '>=', 300)
                  ->orWhereNull('status_code');
            });
        }
        
        return $query->orderBy('created_at', 'desc')->paginate(15);
    }

    public function create(array $data): WebhookLog
    {
        return WebhookLog::create($data);
    }
    
    public function getRecentErrors(int $webhookId, $limit = 5)
    {
        return WebhookLog::where('webhook_id', $webhookId)
            ->where(function($query) {
                $query->where('status_code', '<', 200)
                      ->orWhere('status_code', '>=', 300)
                      ->orWhereNull('status_code');
            })
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
    
    public function getRecentLogs(int $webhookId, $limit = 5)
    {
        return WebhookLog::where('webhook_id', $webhookId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
