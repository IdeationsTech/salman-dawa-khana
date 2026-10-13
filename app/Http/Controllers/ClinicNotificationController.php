<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClinicNotificationController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Notifications — Clinic List
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $validated = $request->validate([
            'status' => ['nullable', 'in:all,unread,read'],
        ]);

        $clinicId = (int) $request->user()->clinic_id;
        $userId = (int) $request->user()->user_id;

        $clinic = DB::table('clinics')
            ->where('clinic_id', $clinicId)
            ->first();

        abort_unless($clinic, 404);

        $base = DB::table('clinic_notifications as notifications')
            ->leftJoin(
                'clinic_notification_reads as reads',
                function ($join) use ($userId) {
                    $join->on(
                        'reads.clinic_notification_id',
                        '=',
                        'notifications.clinic_notification_id'
                    )->where('reads.user_id', '=', $userId);
                }
            )
            ->where('notifications.clinic_id', $clinicId);

        $unreadCount = (clone $base)
            ->whereNull('reads.clinic_notification_read_id')
            ->count();

        $status = $validated['status'] ?? 'all';
        $query = clone $base;

        if ($status === 'unread') {
            $query->whereNull('reads.clinic_notification_read_id');
        } elseif ($status === 'read') {
            $query->whereNotNull('reads.clinic_notification_read_id');
        }

        $notifications = $query
            ->select([
                'notifications.*',
                'reads.read_at',
            ])
            ->orderByDesc('notifications.created_at')
            ->orderByDesc('notifications.clinic_notification_id')
            ->paginate(50)
            ->withQueryString();

        return view('notifications.index', compact(
            'clinic',
            'notifications',
            'unreadCount',
            'status'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | Notifications — Mark One as Read
    |--------------------------------------------------------------------------
    */

    public function markRead(Request $request, int $notification)
    {
        $exists = DB::table('clinic_notifications')
            ->where('clinic_notification_id', $notification)
            ->where('clinic_id', $request->user()->clinic_id)
            ->exists();

        abort_unless($exists, 404);

        $this->saveRead(
            $notification,
            (int) $request->user()->user_id
        );

        return back()->with('success', 'Notification marked as read.');
    }

    /*
    |--------------------------------------------------------------------------
    | Notifications — Mark Current Alerts as Read
    |--------------------------------------------------------------------------
    */

    public function markAllRead(Request $request)
    {
        $clinicId = (int) $request->user()->clinic_id;
        $userId = (int) $request->user()->user_id;

        // Capture a cutoff so alerts arriving later remain unread.
        $cutoff = DB::table('clinic_notifications')
            ->where('clinic_id', $clinicId)
            ->max('clinic_notification_id');

        if ($cutoff !== null) {
            DB::table('clinic_notifications')
                ->where('clinic_id', $clinicId)
                ->where('clinic_notification_id', '<=', $cutoff)
                ->orderBy('clinic_notification_id')
                ->chunkById(
                    200,
                    function ($notifications) use ($userId) {
                        $rows = $notifications->map(fn ($notification) => [
                            'clinic_notification_id' =>
                                $notification->clinic_notification_id,
                            'user_id' => $userId,
                            'read_at' => now(),
                        ])->all();

                        DB::table('clinic_notification_reads')->upsert(
                            $rows,
                            ['clinic_notification_id', 'user_id'],
                            ['user_id']
                        );
                    },
                    'clinic_notification_id'
                );
        }

        return back()->with(
            'success',
            'Current notifications marked as read.'
        );
    }

    private function saveRead(int $notificationId, int $userId): void
    {
        DB::table('clinic_notification_reads')->upsert(
            [
                [
                    'clinic_notification_id' => $notificationId,
                    'user_id' => $userId,
                    'read_at' => now(),
                ],
            ],
            ['clinic_notification_id', 'user_id'],
            ['user_id']
        );
    }
}