<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        try {
            $notifications = DB::table('notifications')
                ->join('profilers', 'notifications.customer_id', '=', 'profilers.id')
                ->join('requested_items', 'notifications.requested_item_id', '=', 'requested_items.id')
                ->select(
                    'notifications.*',
                    'profilers.account_title as customer_name',
                    'profilers.contact_no as customer_phone',
                    'requested_items.medicine_name'
                )
                ->where('notifications.status', 'unread')
                ->orderBy('notifications.created_at', 'DESC')
                ->limit(50)
                ->get();

            return response()->json([
                'success' => true,
                'notifications' => $notifications,
                'count' => $notifications->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function markAsRead(Request $request)
    {
        $request->validate([
            'id' => 'required'
        ]);

        try {
            DB::table('notifications')
                ->where('id', $request->id)
                ->update([
                    'status' => 'read',
                    'updated_at' => now()
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Notification marked as read'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function markAllAsRead()
    {
        try {
            DB::table('notifications')
                ->where('status', 'unread')
                ->update([
                    'status' => 'read',
                    'updated_at' => now()
                ]);

            return response()->json([
                'success' => true,
                'message' => 'All notifications marked as read'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
