<?php

namespace App\Services\Reports;

use App\Enums\LeadStatus;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReportQuery
{
    public const TYPES = ['sales'];

    public const STAFF_TYPES = ['sales'];

    public function statuses(string $type): array
    {
        return $type === 'sales' ? LeadStatus::values() : [];
    }

    public function query(string $type, Request $request, ReportRange $range, User $user)
    {
        abort_unless($type === 'sales', 404);
        $request->validate([
            'status' => ['nullable', Rule::in($this->statuses($type))],
            'user_id' => 'nullable|integer|exists:users,id',
            'service_id' => 'nullable|integer|exists:services,id',
        ]);
        $query = Lead::with('assignee');
        $range->apply($query, 'created_at');
        if ($request->filled('user_id')) {
            $query->where('assigned_to', $request->integer('user_id'));
        }
        if ($request->filled('service_id')) {
            $query->where('service_id', $request->integer('service_id'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        return $query;
    }

    public function columns(string $type): array
    {
        return ['Lead', 'Business', 'Source', 'Service interest', 'Status', 'Estimated value', 'Assigned', 'Created'];
    }

    public function row(string $type, $row): array
    {
        return [$row->name, $row->business_name, $row->source ?? 'Not recorded', $row->service ?? 'Not recorded', $row->status, $row->estimated_value, $row->assignee?->name ?? 'Unassigned', optional($row->created_at)->format('Y-m-d') ?? 'Not recorded'];
    }

    public function link(string $type, $row, User $user): ?string
    {
        return route('admin.leads.show', $row);
    }

    public static function csvSafe(mixed $value): string
    {
        $value = (string) $value;

        return preg_match('/^[\x00-\x20\x{FEFF}]*[=+\-@]/u', $value) ? "'".$value : $value;
    }
}
