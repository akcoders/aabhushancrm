<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ModuleRequest;
use App\Http\Resources\CrmResource;
use App\Services\ActivityService;
use Illuminate\Http\Request;

abstract class CrudController extends Controller
{
    protected string $model;

    protected array $searchable = [];

    protected array $filterable = [];

    protected array $with = [];

    protected ?string $ownershipColumn = null;

    public function index(Request $r)
    {
        $q = ($this->model)::query()->with($this->with);
        $this->scopeOwned($q);
        if ($s = $r->string('search')->toString()) {
            $q->where(fn ($x) => collect($this->searchable)->each(fn ($f, $i) => $i ? $x->orWhere($f, 'like', "%$s%") : $x->where($f, 'like', "%$s%")));
        }foreach ($this->filterable as $f) {
            if ($r->filled($f)) {
                $q->where($f, $r->input($f));
            }
        }$sort = in_array($r->input('sort'), array_merge(['id', 'created_at'], $this->filterable, $this->searchable)) ? $r->input('sort') : 'created_at';
        $q->orderBy($sort, $r->input('direction') === 'asc' ? 'asc' : 'desc');

        return CrmResource::collection($q->paginate(min((int) $r->input('per_page', 15), 100)));
    }

    public function store(ModuleRequest $r, ActivityService $log)
    {
        $m = ($this->model)::create($r->validated() + $this->defaults($r));
        $log->log('created', $m);

        return (new CrmResource($m->load($this->with)))->response()->setStatusCode(201);
    }

    public function show(int $id)
    {
        $model = ($this->model)::with($this->detailWith())->findOrFail($id);
        $this->authorizeOwned($model);
        return new CrmResource($model);
    }

    public function update(ModuleRequest $r, int $id, ActivityService $log)
    {
        $m = ($this->model)::findOrFail($id);
        $this->authorizeOwned($m);
        $old = $m->getOriginal();
        $m->update($r->validated());
        $log->log('updated', $m, ['old' => $old, 'new' => $m->getChanges()]);

        return new CrmResource($m->load($this->with));
    }

    public function destroy(int $id, ActivityService $log)
    {
        $m = ($this->model)::findOrFail($id);
        $this->authorizeOwned($m);
        $m->delete();
        $log->log('deleted', $m);

        return response()->json(['message' => 'Deleted successfully']);
    }

    protected function defaults(Request $r): array
    {
        return [];
    }

    protected function detailWith(): array
    {
        return $this->with;
    }

    protected function isSalesExecutive(): bool
    {
        return auth()->user()?->role?->slug === 'sales-executive';
    }

    protected function scopeOwned($query): void
    {
        if ($this->ownershipColumn && $this->isSalesExecutive()) {
            $query->where($this->ownershipColumn, auth()->id());
        }
    }

    protected function authorizeOwned($model): void
    {
        if ($this->ownershipColumn && $this->isSalesExecutive()) {
            abort_unless((int) $model->{$this->ownershipColumn} === (int) auth()->id(), 403, 'This record is assigned to another salesperson.');
        }
    }
}
