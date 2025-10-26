<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->get('search', ''));
        $user = trim((string) $request->get('user', ''));
        $action = trim((string) $request->get('action', ''));
        $from = trim((string) $request->get('from', ''));
        $to = trim((string) $request->get('to', ''));

        $logs = ActivityLog::query()
            ->with('user:id,name')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('aktivitas', 'like', "%{$search}%")
                      ->orWhereHas('user', function ($q2) use ($search) {
                          $q2->where('name', 'like', "%{$search}%");
                      })
                      ->orWhereRaw("CAST(context AS TEXT) LIKE ?", ["%{$search}%"]);
                });
            })
            ->when($user !== '', function ($query) use ($user) {
                $query->where('user_id', $user);
            })
            ->when($action !== '', function ($query) use ($action) {
                $query->where('aktivitas', $action);
            })
            ->when($from !== '', function ($query) use ($from) {
                $query->whereDate('waktu_aktivitas', '>=', $from);
            })
            ->when($to !== '', function ($query) use ($to) {
                $query->whereDate('waktu_aktivitas', '<=', $to);
            })
            ->latest('waktu_aktivitas')
            ->paginate(20)
            ->withQueryString();

        // Get available users for filter
        $users = User::orderBy('name')->get(['id', 'name']);

        // Get available actions
        $actions = ActivityLog::distinct()
            ->pluck('aktivitas')
            ->filter(fn($value) => !is_null($value) && $value !== '')
            ->sort()
            ->values();

        // KPIs for last 7 days
        $sevenDaysAgo = now()->subDays(7);
        $total7d = ActivityLog::where('waktu_aktivitas', '>=', $sevenDaysAgo)->count();
        $uploads7d = ActivityLog::where('waktu_aktivitas', '>=', $sevenDaysAgo)
            ->where('aktivitas', 'upload_audio')
            ->count();
        $sync7d = ActivityLog::where('waktu_aktivitas', '>=', $sevenDaysAgo)
            ->where('aktivitas', 'device_sync')
            ->count();

        return view('admin.logs.index', compact(
            'logs',
            'users',
            'actions',
            'search',
            'user',
            'action',
            'from',
            'to',
            'total7d',
            'uploads7d',
            'sync7d'
        ));
    }

    /**
     * Remove old activity logs (cleanup/purge).
     */
    public function purge(Request $request)
    {
        $validated = $request->validate([
            'before_date' => ['required', 'date'],
        ]);

        $count = ActivityLog::where('waktu_aktivitas', '<', $validated['before_date'])->delete();

        ActivityLog::create([
            'user_id' => auth()->id(),
            'aktivitas' => 'purge_logs',
            'waktu_aktivitas' => now(),
            'context' => [
                'deleted_count' => $count,
                'before_date' => $validated['before_date'],
            ],
        ]);

        return redirect()
            ->route('admin.logs.index')
            ->with('status', "{$count} log berhasil dihapus.");
    }
}
