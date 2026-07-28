<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use Illuminate\Http\Request;

class AppNotificationController extends Controller
{
    public function index(Request $request)
    {
        return AppNotification::where('user_id', $request->user()->id)->latest()->paginate(50);
    }

    public function read(Request $request, AppNotification $notification)
    {
        abort_unless($notification->user_id === $request->user()->id, 403);
        $notification->update(['read_at' => now()]);
        return $notification;
    }

    public function readAll(Request $request)
    {
        AppNotification::where('user_id', $request->user()->id)->whereNull('read_at')->update(['read_at' => now()]);
        return ['message' => 'All notifications marked as read'];
    }
}
