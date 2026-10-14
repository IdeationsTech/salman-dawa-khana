<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClinicAuditController extends Controller
{
    public function index(Request $request)
    {
        $data = $request->validate([
            'search' => ['nullable', 'string', 'max:160'],
            'action' => ['nullable', 'string', 'max:80'],
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
        ]);

        $clinicId = (int) $request->user()->clinic_id;

        $query = DB::table('audit_logs as logs')
            ->leftJoin('users', function ($join) {
                $join->on('users.user_id', '=', 'logs.user_id')
                    ->on('users.clinic_id', '=', 'logs.clinic_id');
            })
            ->where('logs.clinic_id', $clinicId)
            ->select('logs.*', 'users.name as actor_name');

        if (filled($data['search'] ?? null)) {
            $search = trim($data['search']);

            $query->where(fn ($query) => $query
                ->where('logs.action', 'like', "%{$search}%")
                ->orWhere('logs.entity_type', 'like', "%{$search}%")
                ->orWhere('users.name', 'like', "%{$search}%"));
        }

        if (filled($data['action'] ?? null)) {
            $query->where('logs.action', $data['action']);
        }

        if (filled($data['from'] ?? null)) {
            $query->where('logs.created_at', '>=', $data['from'] . ' 00:00:00');
        }

        if (filled($data['to'] ?? null)) {
            $query->where('logs.created_at', '<=', $data['to'] . ' 23:59:59');
        }

        return view('audit.index', [
            'logs' => $query->orderByDesc('logs.audit_log_id')
                ->paginate(50)->withQueryString(),
            'actions' => DB::table('audit_logs')
                ->where('clinic_id', $clinicId)
                ->distinct()->orderBy('action')->pluck('action'),
        ]);
    }
}