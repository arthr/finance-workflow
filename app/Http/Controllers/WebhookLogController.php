<?php

namespace App\Http\Controllers;

use App\Domain\Webhook\Models\WebhookLog;
use App\Domain\Webhook\Repositories\WebhookLogRepository;
use Illuminate\Http\Request;

class WebhookLogController extends Controller
{
    protected $webhookLogRepository;

    public function __construct(WebhookLogRepository $webhookLogRepository)
    {
        $this->webhookLogRepository = $webhookLogRepository;
    }

    /**
     * Obter detalhes de um log específico
     */
    public function show($id)
    {
        $log = $this->webhookLogRepository->findById($id);

        if (!$log) {
            return response()->json(['message' => 'Log not found'], 404);
        }

        return response()->json($log);
    }
}
