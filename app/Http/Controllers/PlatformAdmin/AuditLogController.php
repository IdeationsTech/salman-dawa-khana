<?php

namespace App\Http\Controllers\PlatformAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:160'],
            'action' => ['nullable', 'string', 'max:100'],
            'admin_id' => ['nullable', 'integer', 'min:1'],
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => [
                'nullable',
                'date_format:Y-m-d',
                Rule::when(
                    $request->filled('from'),
                    ['after_or_equal:from']
                ),
            ],
        ]);

        $filters = [
            'search' => trim($validated['search'] ?? ''),
            'action' => $validated['action'] ?? '',
            'admin_id' => $validated['admin_id'] ?? '',
            'from' => $validated['from'] ?? '',
            'to' => $validated['to'] ?? '',
        ];

        $query = $this->logQuery();

        if ($filters['search'] !== '') {
            $search = '%' . $filters['search'] . '%';

            $query->where(function (Builder $query) use (
                $search,
                $filters
            ) {
                $query
                    ->where('logs.action', 'like', $search)
                    ->orWhere('logs.entity_type', 'like', $search)
                    ->orWhere('clinics.name', 'like', $search)
                    ->orWhere('admins.name', 'like', $search);

                if (ctype_digit($filters['search'])) {
                    $query
                        ->orWhere(
                            'logs.platform_audit_log_id',
                            $filters['search']
                        )
                        ->orWhere(
                            'logs.clinic_id',
                            $filters['search']
                        );
                }
            });
        }

        if ($filters['action'] !== '') {
            $query->where('logs.action', $filters['action']);
        }

        if ($filters['admin_id'] !== '') {
            $query->where(
                'logs.platform_admin_id',
                $filters['admin_id']
            );
        }

        if ($filters['from'] !== '') {
            $query->whereDate(
                'logs.created_at',
                '>=',
                $filters['from']
            );
        }

        if ($filters['to'] !== '') {
            $query->whereDate(
                'logs.created_at',
                '<=',
                $filters['to']
            );
        }

        $logs = $query
            ->orderByDesc('logs.created_at')
            ->orderByDesc('logs.platform_audit_log_id')
            ->paginate(50)
            ->withQueryString();

        $actions = DB::table('platform_audit_logs')
            ->select('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action');

        $admins = DB::table('platform_admins')
            ->select('platform_admin_id', 'name')
            ->orderBy('name')
            ->get();

        return view('platform-admin.audit.index', [
            'logs' => $logs,
            'filters' => $filters,
            'actions' => $actions,
            'admins' => $admins,
        ]);
    }

    public function show(int $log): View
    {
        $record = $this->logQuery()
            ->where('logs.platform_audit_log_id', $log)
            ->first();

        abort_if(! $record, 404);

        return view('platform-admin.audit.show', [
            'log' => $record,
            'oldValues' => $this->formatValues($record->old_values),
            'newValues' => $this->formatValues($record->new_values),
        ]);
    }

    private function logQuery(): Builder
    {
        return DB::table('platform_audit_logs as logs')
            ->leftJoin(
                'platform_admins as admins',
                'admins.platform_admin_id',
                '=',
                'logs.platform_admin_id'
            )
            ->leftJoin(
                'clinics',
                'clinics.clinic_id',
                '=',
                'logs.clinic_id'
            )
            ->select([
                'logs.*',
                'admins.name as admin_name',
                'clinics.name as clinic_name',
            ]);
    }

    private function formatValues(?string $json): array
    {
        if ($json === null) {
            return [];
        }

        $values = json_decode($json, true);

        if (! is_array($values)) {
            return [];
        }

        $formatted = [];

        foreach ($values as $key => $value) {
            if (
                preg_match(
                    '/password|token|secret|authorization/i',
                    (string) $key
                )
            ) {
                $formatted[$key] = '[Hidden]';
                continue;
            }

            $formatted[$key] = match (true) {
                $value === null => 'Not set',
                is_bool($value) => $value ? 'Yes' : 'No',
                is_array($value) => json_encode(
                    $value,
                    JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
                ),
                default => (string) $value,
            };
        }

        return $formatted;
    }
}