<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\Guru;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'target' => 'required|in:all,guru,wali_kelas,specific',
            'specific_users' => 'required_if:target,specific|array'
        ]);

        $notification = new Notification();
        $notification->title = $validated['title'];
        $notification->content = $validated['content'];
        $notification->target = $validated['target'];
        
        if ($validated['target'] === 'specific') {
            $notification->specific_users = json_encode($request->specific_users);
        }
        
        $notification->save();

        return response()->json([
            'success' => true,
            'message' => 'Notifikasi berhasil ditambahkan'
        ]);
    }

    public function destroy(Notification $notification)
    {
        $notification->delete();
        return response()->json(['success' => true]);
    }

    public function index()
    {
        $guru = Auth::guard('guru')->user();
        $role = session('selected_role');

        return Notification::where(function($query) use ($guru, $role) {
            $query->where('target', 'all')
                  ->orWhere('target', $role)
                  ->orWhere(function($q) use ($guru) {
                      $q->where('target', 'specific')
                        ->whereRaw("JSON_CONTAINS(specific_users, ?)", [$guru->id]);
                  });
        })
        ->latest()
        ->get();
    }

    public function markAsRead(Notification $notification)
    {
        $guru = Auth::guard('guru')->user();
        
        if (!$notification->readers()->where('guru_id', $guru->id)->exists()) {
            $notification->readers()->attach($guru->id, ['read_at' => now()]);
        }

        return response()->json(['success' => true]);
    }

    public function getUnreadCount()
    {
        $guru = Auth::guard('guru')->user();
        $role = session('selected_role');
        
        $count = Notification::where(function($query) use ($guru, $role) {
            $query->where('target', 'all')
                  ->orWhere('target', $role)
                  ->orWhere(function($q) use ($guru) {
                      $q->where('target', 'specific')
                        ->whereRaw("JSON_CONTAINS(specific_users, ?)", [$guru->id]);
                  });
        })
        ->whereDoesntHave('readers', function($query) use ($guru) {
            $query->where('guru_id', $guru->id);
        })
        ->count();

        return response()->json(['count' => $count]);
    }
}