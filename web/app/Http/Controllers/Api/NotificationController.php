<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Services\TelegramService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    protected TelegramService $telegram;

    public function __construct(TelegramService $telegram)
    {
        $this->telegram = $telegram;
    }

    /**
     * Get list of notifications for the authenticated user
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $notifications = Notification::where('user_id', $user->id)
            ->with(['booking.dacha:id,name,region,district,address'])
            ->latest()
            ->paginate(20);

        $unreadCount = Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->count();

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
            'has_telegram_linked' => !empty($user->telegram_id),
            'telegram_id' => $user->telegram_id,
        ]);
    }

    /**
     * Mark single notification as read
     */
    public function markAsRead(Request $request, $id)
    {
        $notification = Notification::where('user_id', $request->user()->id)
            ->where('id', $id)
            ->firstOrFail();

        $notification->update(['is_read' => true]);

        return response()->json([
            'message' => 'Bildirishnoma o\'qilgan deb belgilandi.',
            'notification' => $notification,
        ]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(Request $request)
    {
        Notification::where('user_id', $request->user()->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json([
            'message' => 'Barcha bildirishnomalar o\'qilgan deb belgilandi.',
        ]);
    }

    /**
     * Get Telegram Bot deep link for account binding
     */
    public function getTelegramBotLink(Request $request)
    {
        $user = $request->user();
        $botUsername = $this->telegram->getBotUsername() ?? 'OromgoBot';
        $link = "https://t.me/{$botUsername}?start=bind_{$user->id}";

        return response()->json([
            'bot_username' => $botUsername,
            'link' => $link,
            'is_linked' => !empty($user->telegram_id),
            'telegram_id' => $user->telegram_id,
        ]);
    }
}
