<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\SupportMessage;
use App\Models\User;
use Illuminate\Http\Request;

class SupportController extends Controller
{
    /**
     * Foydalanuvchining Admin bilan yozishmalari tarixi
     */
    public function getMessages(Request $request)
    {
        $userId = $request->user()->id;

        // Admin tomonidan yuborilgan o'qilmagan xabarlarni o'qilgan deb belgilash
        SupportMessage::where('user_id', $userId)
            ->where('sender_type', 'admin')
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $messages = SupportMessage::where('user_id', $userId)
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json($messages);
    }

    /**
     * Foydalanuvchi tomonidan Adminga xabar yuborish
     */
    public function sendMessage(Request $request)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:3000',
        ]);

        $msg = SupportMessage::create([
            'user_id' => $request->user()->id,
            'sender_type' => 'user',
            'message' => trim($validated['message']),
            'is_read' => false,
        ]);

        return response()->json([
            'message' => 'Xabaringiz adminga yuborildi',
            'data' => $msg
        ], 201);
    }

    /**
     * Admin uchun: Barcha murojaat qilgan foydalanuvchilar (suhbatlar) ro'yxati
     */
    public function adminGetConversations()
    {
        $userIds = SupportMessage::select('user_id')
            ->distinct()
            ->pluck('user_id');

        $users = User::whereIn('id', $userIds)->get();

        $conversations = $users->map(function ($u) {
            $lastMessage = SupportMessage::where('user_id', $u->id)
                ->latest()
                ->first();

            $unreadCount = SupportMessage::where('user_id', $u->id)
                ->where('sender_type', 'user')
                ->where('is_read', false)
                ->count();

            return [
                'user' => [
                    'id' => $u->id,
                    'name' => $u->name,
                    'phone' => $u->phone,
                    'role' => $u->role,
                    'avatar' => $u->avatar,
                ],
                'last_message' => $lastMessage ? [
                    'message' => $lastMessage->message,
                    'sender_type' => $lastMessage->sender_type,
                    'created_at' => $lastMessage->created_at->toIso8601String(),
                ] : null,
                'unread_count' => $unreadCount,
            ];
        })->sortByDesc(function ($item) {
            return $item['last_message']['created_at'] ?? '';
        })->values();

        return response()->json($conversations);
    }

    /**
     * Admin uchun: Tanlangan foydalanuvchi bilan yozishmalar tarixi
     */
    public function adminGetMessages($userId)
    {
        $user = User::findOrFail($userId);

        // Foydalanuvchining o'qilmagan xabarlarini o'qildi deb belgilash
        SupportMessage::where('user_id', $userId)
            ->where('sender_type', 'user')
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $messages = SupportMessage::where('user_id', $userId)
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'user' => $user,
            'messages' => $messages,
        ]);
    }

    /**
     * Admin tomonidan foydalanuvchiga javob yozish
     */
    public function adminReply(Request $request, $userId)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:3000',
        ]);

        $user = User::findOrFail($userId);

        $msg = SupportMessage::create([
            'user_id' => $user->id,
            'sender_type' => 'admin',
            'message' => trim($validated['message']),
            'is_read' => false,
        ]);

        // Foydalanuvchiga bildirishnoma (Notification) ham jo'natamiz
        Notification::create([
            'user_id' => $user->id,
            'title' => '💬 Admindan yangi javob xabari',
            'message' => 'Administrator sizning murojaatingizga javob berdi: "' . mb_substr($msg->message, 0, 80) . '..."',
            'type' => 'info',
            'is_read' => false,
        ]);

        return response()->json([
            'message' => 'Javobingiz foydalanuvchiga yetkazildi',
            'data' => $msg
        ], 201);
    }
}
