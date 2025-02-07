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

        try {
            $notification = new Notification();
            $notification->title = $validated['title'];
            $notification->content = $validated['content'];
            $notification->target = $validated['target'];
            
            if ($validated['target'] === 'specific') {
                $notification->specific_users = array_map('intval', $validated['specific_users']);
            }
            
            $notification->save();

            // Return the newly created notification with additional data
            return response()->json([
                'success' => true,
                'message' => 'Notifikasi berhasil ditambahkan',
                'notification' => [
                    'id' => $notification->id,
                    'title' => $notification->title,
                    'content' => $notification->content,
                    'target' => $notification->target,
                    'created_at' => $notification->created_at->diffForHumans(),
                    'is_read' => false
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Notification creation error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan notifikasi'
            ], 500);
        }
    }

    public function list()
    {
        $notifications = Notification::latest()->get()->map(function($notification) {
            return [
                'id' => $notification->id,
                'title' => $notification->title,
                'content' => $notification->content,
                'created_at' => $notification->created_at->diffForHumans()
            ];
        });

        return response()->json(['items' => $notifications]);
    }
    public function index()
    {
        $guru = Auth::guard('guru')->user();
        $selected_role = session('selected_role');
    
        $notifications = Notification::where(function($query) use ($guru, $selected_role) {
            $query->where('target', 'all')
                  ->orWhere('target', $selected_role) // Menggunakan selected_role dari session
                  ->orWhere(function($q) use ($guru) {
                      $q->where('target', 'specific')
                        ->whereJsonContains('specific_users', $guru->id);
                  });
        })
        ->orderBy('created_at', 'desc') // Tambahkan ordering
        ->take(5) // Batasi 5 notifikasi terakhir
        ->get()
        ->map(function ($notification) use ($guru) {
            return [
                'id' => $notification->id,
                'title' => $notification->title,
                'content' => $notification->content,
                'created_at' => $notification->created_at->diffForHumans(),
                'is_read' => $notification->isReadBy($guru->id)
            ];
        });
    
        return response()->json(['items' => $notifications]);
    }
    
    public function markAsRead(Notification $notification)
    {
        try {
            $guru = Auth::guard('guru')->user();
            
            if (!$notification->readers()->where('guru_id', $guru->id)->exists()) {
                $notification->readers()->attach($guru->id, [
                    'read_at' => now()
                ]);
            }
    
            return response()->json([
                'success' => true,
                'message' => 'Notifikasi telah dibaca'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error marking notification as read: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menandai notifikasi sebagai telah dibaca'
            ], 500);
        }
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

    public function destroy(Notification $notification)
    {
        try {
            $notification->delete();
            return response()->json([
                'success' => true,
                'message' => 'Notifikasi berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error deleting notification: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus notifikasi'
            ], 500);
        }
    }
}