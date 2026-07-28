<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\ModuleRequest;
use App\Models\Lead;
use App\Models\LeadHistory;
use App\Services\ActivityService;
use App\Services\CustomerJourneyService;
use App\Services\LeadService;
use Illuminate\Http\Request;

class LeadController extends CrudController
{
    protected string $model = Lead::class;

    protected array $searchable = ['name', 'mobile', 'email'];

    protected array $filterable = ['status', 'source', 'priority', 'assigned_to', 'exhibition_id'];

    protected array $with = ['assignee', 'exhibition'];

    protected function detailWith(): array
    {
        return ['assignee', 'exhibition', 'notes.user', 'followups.assignee', 'history.user', 'customer'];
    }

    protected function defaults(Request $r): array
    {
        return ['created_by' => auth()->id()];
    }

    public function store(ModuleRequest $r, ActivityService $log)
    {
        $dupes = app(LeadService::class)->duplicates($r->mobile, $r->email);
        if ($dupes->isNotEmpty() && ! $r->boolean('allow_duplicate')) {
            return response()->json(['message' => 'Possible duplicate lead found.', 'duplicates' => $dupes], 422);
        }

        return parent::store($r, $log);
    }

    public function update(ModuleRequest $r, int $id, ActivityService $log)
    {
        $lead = Lead::findOrFail($id);
        $old = $lead->toArray();
        $response = parent::update($r, $id, $log);
        LeadHistory::create(['lead_id' => $id, 'user_id' => auth()->id(), 'action' => 'updated', 'old_values' => $old, 'new_values' => $r->validated()]);

        return $response;
    }

    public function convert(Lead $lead, LeadService $service)
    {
        return response()->json(['message' => 'Lead converted successfully', 'customer' => $service->convert($lead)]);
    }

    public function addNote(Request $r, Lead $lead)
    {
        $d = $r->validate(['note' => 'required|string', 'attachments' => 'nullable|array']);

        return response()->json($lead->notes()->create($d + ['user_id' => auth()->id()])->load('user'), 201);
    }

    public function journey(Lead $lead, CustomerJourneyService $service)
    {
        return $service->forLead($lead);
    }

    public function export(Request $r)
    {
        $rows = Lead::with('assignee')->get();
        $csv = "Name,Mobile,Email,Source,Status,Priority,Assigned To\n".$rows->map(fn ($x) => collect([$x->name, $x->mobile, $x->email, $x->source, $x->status, $x->priority, $x->assignee?->name])->map(fn ($v) => '"'.str_replace('"', '""', $v).'"')->join(','))->join("\n");

        return response($csv)->header('Content-Type', 'text/csv')->header('Content-Disposition', 'attachment; filename=leads.csv');
    }

    public function import(Request $r)
    {
        $r->validate(['file' => 'required|file|mimes:csv,txt']);
        $handle = fopen($r->file('file')->getRealPath(), 'r');
        $headers = array_map(fn ($x) => strtolower(str_replace(' ', '_', trim($x))), fgetcsv($handle));
        $count = 0;
        while (($row = fgetcsv($handle)) !== false) {
            $d = array_combine($headers, $row);
            if (! empty($d['name']) && ! empty($d['mobile'])) {
                Lead::create(['name' => $d['name'], 'mobile' => $d['mobile'], 'email' => $d['email'] ?? null, 'source' => $d['source'] ?? 'Import', 'status' => $d['status'] ?? 'New', 'priority' => $d['priority'] ?? 'Warm', 'created_by' => auth()->id()]);
                $count++;
            }
        }fclose($handle);

        return ['message' => "$count leads imported", 'count' => $count];
    }
}
