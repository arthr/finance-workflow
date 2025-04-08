<?php

namespace App\Http\Controllers;

use App\Domain\Webhook\Models\Webhook;
use App\Domain\Webhook\Repositories\WebhookRepository;
use App\Domain\Workflow\Repositories\WorkflowRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class WebhookController extends Controller
{
    protected $webhookRepository;
    protected $workflowRepository;

    public function __construct(WebhookRepository $webhookRepository, WorkflowRepository $workflowRepository)
    {
        $this->webhookRepository = $webhookRepository;
        $this->workflowRepository = $workflowRepository;
    }

    public function index(Request $request)
    {
        $search = $request->get('search');
        $status = $request->get('status');
        $workflowId = $request->get('workflow_id');

        $webhooks = $this->webhookRepository->findWithFilters($search, $status, $workflowId);
        $workflows = $this->workflowRepository->findAll();

        if ($request->wantsJson()) {
            return response()->json($webhooks);
        }

        return view('webhooks.index', compact('workflowId', 'webhooks', 'workflows'));
    }

    public function create(Request $request)
    {
        $workflows = $this->workflowRepository->findAll();

        // Pré-seleção do workflow se for chamado a partir da página de workflow
        $workflowId = $request->get('workflow_id');

        return view('webhooks.create', compact('workflows', 'workflowId'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'url' => 'required|url',
            'secret' => 'nullable|string',
            'events' => 'required|array',
            'workflow_id' => 'nullable|exists:workflows,id',
            'is_active' => 'boolean',
            'max_retries' => 'nullable|integer|min:0|max:10',
        ]);

        if ($validator->fails()) {
            if ($request->wantsJson()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Processar cabeçalhos personalizados
        $headers = [];
        $headerKeys = $request->input('header_keys', []);
        $headerValues = $request->input('header_values', []);

        for ($i = 0; $i < count($headerKeys); $i++) {
            if (!empty($headerKeys[$i]) && isset($headerValues[$i])) {
                $headers[$headerKeys[$i]] = $headerValues[$i];
            }
        }

        // Preparar dados para criar o webhook
        $data = $request->all();
        $data['headers'] = $headers;

        // Definir secret aleatório se não for fornecido
        if (empty($data['secret'])) {
            $data['secret'] = Str::random(32);
        }

        // Garantir que is_active seja um booleano
        $data['is_active'] = $request->has('is_active');

        $webhook = $this->webhookRepository->create($data);

        if ($request->wantsJson()) {
            return response()->json($webhook, 201);
        }

        return redirect()->route('webhooks.index')
            ->with('success', 'Webhook criado com sucesso!');
    }

    public function show($id, Request $request)
    {
        $webhook = $this->webhookRepository->findById($id);

        if (!$webhook) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Webhook not found'], 404);
            }

            return redirect()->route('webhooks.index')
                ->with('error', 'Webhook não encontrado.');
        }

        // Filtrar logs se necessário
        $event = $request->get('event');
        $status = $request->get('status');

        $logs = $this->webhookRepository->findLogsWithFilters($id, $event, $status);

        if ($request->wantsJson()) {
            return response()->json(['webhook' => $webhook, 'logs' => $logs]);
        }

        return view('webhooks.show', compact('webhook', 'logs'));
    }

    public function edit($id)
    {
        $webhook = $this->webhookRepository->findById($id);

        if (!$webhook) {
            return redirect()->route('webhooks.index')
                ->with('error', 'Webhook não encontrado.');
        }

        $workflows = $this->workflowRepository->findAll();

        return view('webhooks.edit', compact('webhook', 'workflows'));
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'string|max:255',
            'description' => 'nullable|string',
            'url' => 'url',
            'secret' => 'nullable|string',
            'events' => 'array',
            'workflow_id' => 'nullable|exists:workflows,id',
            'max_retries' => 'nullable|integer|min:0|max:10',
        ]);

        if ($validator->fails()) {
            if ($request->wantsJson()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Processar cabeçalhos personalizados
        $headers = [];
        $headerKeys = $request->input('header_keys', []);
        $headerValues = $request->input('header_values', []);

        for ($i = 0; $i < count($headerKeys); $i++) {
            if (!empty($headerKeys[$i]) && isset($headerValues[$i])) {
                $headers[$headerKeys[$i]] = $headerValues[$i];
            }
        }

        // Preparar dados para atualizar o webhook
        $data = $request->all();
        $data['headers'] = $headers;

        // Garantir que is_active seja um booleano
        $data['is_active'] = $request->has('is_active');

        $webhook = $this->webhookRepository->update($id, $data);

        if (!$webhook) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Webhook not found'], 404);
            }

            return redirect()->route('webhooks.index')
                ->with('error', 'Webhook não encontrado.');
        }

        if ($request->wantsJson()) {
            return response()->json($webhook);
        }

        return redirect()->route('webhooks.index')
            ->with('success', 'Webhook atualizado com sucesso!');
    }

    public function destroy($id, Request $request)
    {
        $result = $this->webhookRepository->delete($id);

        if (!$result) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Webhook not found'], 404);
            }

            return redirect()->route('webhooks.index')
                ->with('error', 'Webhook não encontrado.');
        }

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Webhook deleted successfully']);
        }

        return redirect()->route('webhooks.index')
            ->with('success', 'Webhook excluído com sucesso!');
    }

    public function logs($id, Request $request)
    {
        $webhook = $this->webhookRepository->findById($id);

        if (!$webhook) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Webhook not found'], 404);
            }

            return redirect()->route('webhooks.index')
                ->with('error', 'Webhook não encontrado.');
        }

        $logs = $this->webhookRepository->findLogs($id);

        if ($request->wantsJson()) {
            return response()->json($logs);
        }

        return response()->json($logs); // API only endpoint
    }
}
