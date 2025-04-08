<?php

namespace App\Domain\Webhook\Repositories;

use App\Domain\Webhook\Models\Webhook;
use App\Domain\Webhook\Models\WebhookLog;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class WebhookRepository
{
    protected $webhookLogRepository;

    public function __construct(WebhookLogRepository $webhookLogRepository)
    {
        $this->webhookLogRepository = $webhookLogRepository;
    }

    public function findAll(): Collection
    {
        return Webhook::all();
    }

    public function findById(int $id): ?Webhook
    {
        return Webhook::find($id);
    }

    public function findWithFilters($search = null, $status = null, $workflowId = null)
    {
        $query = Webhook::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('url', 'like', "%{$search}%");
            });
        }

        if ($status === 'active') {
            $query->where('is_active', true);
        } elseif ($status === 'inactive') {
            $query->where('is_active', false);
        }

        if ($workflowId) {
            $query->where('workflow_id', $workflowId);
        }

        return $query->orderBy('created_at', 'desc')->paginate(10);
    }

    public function create(array $data): Webhook
    {
        return Webhook::create($data);
    }

    public function update(int $id, array $data): ?Webhook
    {
        $webhook = $this->findById($id);

        if (!$webhook) {
            return null;
        }

        $webhook->update($data);
        return $webhook;
    }

    public function delete(int $id): bool
    {
        $webhook = $this->findById($id);

        if (!$webhook) {
            return false;
        }

        return $webhook->delete();
    }

    public function findLogs(int $webhookId, int $limit = 50): Collection
    {
        return WebhookLog::where('webhook_id', $webhookId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function findLogsWithFilters(int $webhookId, $event = null, $status = null): LengthAwarePaginator
    {
        return $this->webhookLogRepository->findByWebhookId($webhookId, $event, $status);
    }
}
