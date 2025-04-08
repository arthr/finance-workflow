<?php

namespace App\Domain\Workflow\Repositories;

use App\Domain\Workflow\Models\Workflow;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class WorkflowRepository
{
    public function findAll(): Collection
    {
        return Workflow::all();
    }

    public function findById(int $id): ?Workflow
    {
        return Workflow::find($id);
    }

    public function findWithFilters($search = null, $status = null): LengthAwarePaginator
    {
        $query = Workflow::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($status === 'active') {
            $query->where('is_active', true);
        } elseif ($status === 'inactive') {
            $query->where('is_active', false);
        }

        return $query->orderBy('created_at', 'desc')->paginate(10);
    }

    public function create(array $data): Workflow
    {
        return Workflow::create($data);
    }

    public function update(int $id, array $data): ?Workflow
    {
        $workflow = $this->findById($id);

        if (!$workflow) {
            return null;
        }

        $workflow->update($data);
        return $workflow;
    }

    public function delete(int $id): bool
    {
        $workflow = $this->findById($id);

        if (!$workflow) {
            return false;
        }

        return $workflow->delete();
    }

    public function findWithStages(int $id): ?Workflow
    {
        return Workflow::with(['stages' => function ($query) {
            $query->orderBy('order');
        }])->find($id);
    }

    public function findWithStagesAndTransitions(int $id): ?Workflow
    {
        return Workflow::with([
            'stages' => function ($query) {
                $query->orderBy('order');
            },
            'transitions'
        ])->find($id);
    }
}
