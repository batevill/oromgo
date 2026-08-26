<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Notification;
use App\Models\User;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TelegramWebhookController extends Controller
{
    protected TelegramService $telegram;

    public function __construct(TelegramService $telegram)
    {
        $this->telegram = $telegram;
    }

    /**
     * Telegram Webhook Handler
     */
    public function handle(Request $request)
    {
        $update = $request->all();
        Log::info('Telegram Webhook incoming update', ['update' => $update]);

        // 1. Handle Callback Query (Buttons: Confirm / Reject)
        if (isset($update['callback_query'])) {
            return $this->handleCallbackQuery($update['callback_query']);
        }

        // 2. Handle standard Messages (/start bind_..., etc.)
        if (isset($update['message'])) {
            return $this->handleMessage($update['message']);
        }

        return response()->json(['status' => 'ok']);
    }

    /**
     * Handle incoming text messages & commands
     */
    protected function handleMessage(array $message)
    {
        $chatId = $message['chat']['id'] ?? null;
        $text = trim($message['text'] ?? '');
        $fromUsername = $message['from']['username'] ?? '';
        $fromName = trim(($message['from']['first_name'] ?? '') . ' ' . ($message['from']['last_name'] ?? ''));

        if (!$chatId) {
            return response()->json(['status' => 'no_chat_id']);
        }

        // Deep linking: /start bind_123
        if (str_starts_with($text, '/start bind_')) {
            $userId = (int) str_replace('/start bind_', '', $text);
            $user = User::find($userId);

            if ($user) {
                $user->update(['telegram_id' => (string) $chatId]);

                $this->telegram->sendMessage(
                    $chatId,
                    "🎉 <b>Tabriklaymiz, {$user->name}!</b>\n\n"
                    . "Sizning Telegram profilingiz <b>Oromgo</b> hisobingizga muvaffaqiyatli bog'landi.\n\n"
                    . "Endi siz:\n"
                    . "• Yangi bron tushganda darhol bot orqali xabar olasiz\n"
                    . "• Botdagi tugmalar orqali bronlarni 1 bosishda tasdiqlaysiz yoki rad etasiz\n"
                    . "• Bron statuslari va eslatmalarni real vaqtda kuzatasiz 🚀"
                );

                // Create In-App Notification
                Notification::create([
                    'user_id' => $user->id,
                    'type' => 'info',
                    'title' => 'Telegram bot bog\'landi',
                    'message' => 'Telegram profilingiz muvaffaqiyatli ulandi. Endi barcha xabarlar botga ham yuboriladi.',
                    'data' => [
                        'telegram_id' => (string) $chatId,
                        'telegram_username' => $fromUsername,
                    ],
                ]);

                return response()->json(['status' => 'bind_success']);
            } else {
                $this->telegram->sendMessage(
                    $chatId,
                    "⚠️ Foydalanuvchi topilmadi. Iltimos, Oromgo saytiga kiring va profilingizdagi 'Telegram botga ulanish' tugmasini qayta bosing."
                );
                return response()->json(['status' => 'user_not_found']);
            }
        }

        // Standard /start or greeting
        if (str_starts_with($text, '/start')) {
            // Check if this telegram_id already belongs to any user
            $user = User::where('telegram_id', (string) $chatId)->first();

            if ($user) {
                $this->telegram->sendMessage(
                    $chatId,
                    "👋 <b>Assalomu alaykum, {$user->name}!</b>\n\n"
                    . "Siz Oromgo rasmiy botidasiz. Sizning hisobingiz tizimga ulangan.\n\n"
                    . "🏡 <b>Mavjud buyruqlar:</b>\n"
                    . "/my_bookings — Mening bronlarim ro'yxati\n"
                    . "/help — Yordam va ma'lumot"
                );
            } else {
                $this->telegram->sendMessage(
                    $chatId,
                    "👋 <b>Assalomu alaykum!</b>\n\n"
                    . "Oromgo — O'zbekistonning so'lim dachalari platformasining rasmiy botiga xush kelibsiz! 🏡\n\n"
                    . "Botdan to'liq foydalanish va bronlar haqida jonli xabarnomalarni olish uchun saytimizga kiring va profilingizni bot bilan bog'lang.\n\n"
                    . "🌐 Sayt: <a href=\"" . config('app.url') . "\">Oromgo.uz</a>"
                );
            }

            return response()->json(['status' => 'start_processed']);
        }

        // /my_bookings command
        if ($text === '/my_bookings') {
            $user = User::where('telegram_id', (string) $chatId)->first();

            if (!$user) {
                $this->telegram->sendMessage($chatId, "⚠️ Hisobingiz tizim bilan bog'lanmagan. Saytimiz orqali ulaning.");
                return response()->json(['status' => 'not_linked']);
            }

            if (in_array($user->role, ['owner', 'admin', 'super_admin'])) {
                $bookings = Booking::whereHas('dacha', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                })->latest()->take(5)->get();

                if ($bookings->isEmpty()) {
                    $this->telegram->sendMessage($chatId, "📭 Hozircha dachangizga kelgan bron so'rovlari mavjud emas.");
                } else {
                    $msg = "📋 <b>Oxirgi 5 ta bron so'rovingiz:</b>\n\n";
                    foreach ($bookings as $b) {
                        $statusEmoji = match ($b->status) {
                            'confirmed' => '✅ Tasdiqlangan',
                            'cancelled' => '❌ Bekor qilingan',
                            default => '⏳ Kutilmoqda',
                        };
                        $msg .= "• <b>{$b->dacha->name}</b>\n  📅 {$b->start_date->format('Y-m-d')} — {$b->end_date->format('Y-m-d')}\n  💰 {$b->total_price} {$b->currency} ({$statusEmoji})\n\n";
                    }
                    $this->telegram->sendMessage($chatId, $msg);
                }
            } else {
                $bookings = Booking::where('user_id', $user->id)->latest()->take(5)->get();

                if ($bookings->isEmpty()) {
                    $this->telegram->sendMessage($chatId, "📭 Sizda hali bronlar yo'q.");
                } else {
                    $msg = "📋 <b>Sizning oxirgi 5 ta broningiz:</b>\n\n";
                    foreach ($bookings as $b) {
                        $statusEmoji = match ($b->status) {
                            'confirmed' => '✅ Tasdiqlangan',
                            'cancelled' => '❌ Bekor qilingan',
                            default => '⏳ Kutilmoqda',
                        };
                        $msg .= "• <b>{$b->dacha->name}</b>\n  📅 {$b->start_date->format('Y-m-d')} — {$b->end_date->format('Y-m-d')}\n  💰 {$b->total_price} {$b->currency} ({$statusEmoji})\n\n";
                    }
                    $this->telegram->sendMessage($chatId, $msg);
                }
            }

            return response()->json(['status' => 'my_bookings_sent']);
        }

        // Default response
        $this->telegram->sendMessage($chatId, "Salom! Buyruqlar haqida bilish uchun /help yoki /my_bookings ni yuboring.");
        return response()->json(['status' => 'default_replied']);
    }

    /**
     * Handle Inline button clicks (Tasdiqlash / Rad etish)
     */
    protected function handleCallbackQuery(array $callbackQuery)
    {
        $callbackId = $callbackQuery['id'] ?? '';
        $data = $callbackQuery['data'] ?? '';
        $fromId = (string) ($callbackQuery['from']['id'] ?? '');
        $messageId = $callbackQuery['message']['message_id'] ?? null;
        $chatId = $callbackQuery['message']['chat']['id'] ?? null;

        if (str_starts_with($data, 'confirm_booking:')) {
            $bookingId = (int) str_replace('confirm_booking:', '', $data);
            return $this->processBookingDecision($bookingId, 'confirm', $fromId, $chatId, $messageId, $callbackId);
        }

        if (str_starts_with($data, 'reject_booking:')) {
            $bookingId = (int) str_replace('reject_booking:', '', $data);
            return $this->processBookingDecision($bookingId, 'reject', $fromId, $chatId, $messageId, $callbackId);
        }

        $this->telegram->answerCallbackQuery($callbackId, 'Noma\'lum amal');
        return response()->json(['status' => 'unknown_action']);
    }

    /**
     * Process Approve or Reject decisions from Telegram Bot
     */
    protected function processBookingDecision(int $bookingId, string $action, string $telegramUserId, $chatId, $messageId, string $callbackId)
    {
        $booking = Booking::with(['dacha.user', 'user'])->find($bookingId);

        if (!$booking) {
            $this->telegram->answerCallbackQuery($callbackId, '⚠️ Bron topilmadi yoki o\'chirilgan.', true);
            return response()->json(['error' => 'Booking not found'], 404);
        }

        $owner = $booking->dacha->user ?? null;

        // Security check: verify this telegram user is indeed the owner or an admin
        $isOwner = $owner && (string) $owner->telegram_id === $telegramUserId;
        $user = User::where('telegram_id', $telegramUserId)->first();
        $isAdmin = $user && in_array($user->role, ['admin', 'super_admin']);

        if (!$isOwner && !$isAdmin) {
            $this->telegram->answerCallbackQuery($callbackId, '⛔ Siz ushbu dacha egasi emassiz!', true);
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($action === 'confirm') {
            if ($booking->status === 'confirmed') {
                $this->telegram->answerCallbackQuery($callbackId, 'ℹ️ Ushbu bron allaqachon tasdiqlangan.');
                return response()->json(['status' => 'already_confirmed']);
            }

            $booking->update(['status' => 'confirmed']);

            // Auto-cancel overlapping pending requests
            Booking::where('dacha_id', $booking->dacha_id)
                ->where('id', '!=', $booking->id)
                ->where('status', 'pending')
                ->where(function ($q) use ($booking) {
                    $q->where('start_date', '<=', $booking->end_date)
                      ->where('end_date', '>=', $booking->start_date);
                })
                ->update(['status' => 'cancelled']);

            // Update Telegram Message for Owner
            $updatedText = "✅ <b>BRON TASDIQLANDI!</b>\n\n"
                . "🏡 <b>Dacha:</b> " . htmlspecialchars($booking->dacha->name) . "\n"
                . "👤 <b>Mijoz:</b> " . htmlspecialchars($booking->user->name ?? 'Mijoz') . "\n"
                . "📞 <b>Telefon:</b> " . htmlspecialchars($booking->user->phone ?? '-') . "\n"
                . "📅 <b>Sanalar:</b> <code>{$booking->start_date->format('Y-m-d')}</code> — <code>{$booking->end_date->format('Y-m-d')}</code>\n"
                . "💰 <b>Summa:</b> " . number_format($booking->total_price, 0, '.', ' ') . " {$booking->currency}\n\n"
                . "<i>Siz ushbu bronni tasdiqladingiz. Kalendarda sanalar band qilindi.</i>";

            $this->telegram->editMessageText($chatId, $messageId, $updatedText, []);
            $this->telegram->answerCallbackQuery($callbackId, '✅ Bron muvaffaqiyatli tasdiqlandi!');

            // In-App Notification for Customer
            Notification::create([
                'user_id' => $booking->user_id,
                'booking_id' => $booking->id,
                'type' => 'booking_confirmed',
                'title' => 'Broningiz tasdiqlandi! 🎉',
                'message' => "{$booking->dacha->name} dachasiga yuborgan bron so'rovingiz tasdiqlandi.",
                'data' => [
                    'booking_id' => $booking->id,
                    'dacha_id' => $booking->dacha_id,
                    'dacha_name' => $booking->dacha->name,
                    'start_date' => $booking->start_date->format('Y-m-d'),
                    'end_date' => $booking->end_date->format('Y-m-d'),
                    'total_price' => $booking->total_price,
                    'currency' => $booking->currency,
                    'status' => 'confirmed',
                ],
            ]);

            // In-App Notification for Owner
            if ($owner) {
                Notification::create([
                    'user_id' => $owner->id,
                    'booking_id' => $booking->id,
                    'type' => 'booking_confirmed',
                    'title' => 'Bron tasdiqlandi ✅',
                    'message' => "{$booking->user->name} ning {$booking->dacha->name} dachasiga qilgan broni tasdiqlandi.",
                    'data' => [
                        'booking_id' => $booking->id,
                        'dacha_name' => $booking->dacha->name,
                        'guest_name' => $booking->user->name,
                        'guest_phone' => $booking->user->phone,
                        'start_date' => $booking->start_date->format('Y-m-d'),
                        'end_date' => $booking->end_date->format('Y-m-d'),
                        'total_price' => $booking->total_price,
                        'status' => 'confirmed',
                    ],
                ]);
            }

            // Send notification to customer via Telegram
            $this->telegram->sendBookingConfirmedToCustomer($booking);

            return response()->json(['status' => 'confirmed']);
        } elseif ($action === 'reject') {
            $booking->update(['status' => 'cancelled']);

            // Update Telegram Message for Owner
            $updatedText = "❌ <b>BRON SO'ROVI RAD ETILDI</b>\n\n"
                . "🏡 <b>Dacha:</b> " . htmlspecialchars($booking->dacha->name) . "\n"
                . "👤 <b>Mijoz:</b> " . htmlspecialchars($booking->user->name ?? 'Mijoz') . "\n"
                . "📅 <b>Sanalar:</b> <code>{$booking->start_date->format('Y-m-d')}</code> — <code>{$booking->end_date->format('Y-m-d')}</code>\n\n"
                . "<i>Ushbu bron so'rovi rad etildi va bekor qilindi.</i>";

            $this->telegram->editMessageText($chatId, $messageId, $updatedText, []);
            $this->telegram->answerCallbackQuery($callbackId, '❌ Bron so\'rovi rad etildi.');

            // In-App Notification for Customer
            Notification::create([
                'user_id' => $booking->user_id,
                'booking_id' => $booking->id,
                'type' => 'booking_cancelled',
                'title' => 'Broningiz rad etildi ❌',
                'message' => "{$booking->dacha->name} dachasiga yuborgan bron so'rovingiz egasi tomonidan rad etildi.",
                'data' => [
                    'booking_id' => $booking->id,
                    'dacha_id' => $booking->dacha_id,
                    'dacha_name' => $booking->dacha->name,
                    'start_date' => $booking->start_date->format('Y-m-d'),
                    'end_date' => $booking->end_date->format('Y-m-d'),
                    'status' => 'cancelled',
                ],
            ]);

            // In-App Notification for Owner
            if ($owner) {
                Notification::create([
                    'user_id' => $owner->id,
                    'booking_id' => $booking->id,
                    'type' => 'booking_cancelled',
                    'title' => 'Bron rad etildi ❌',
                    'message' => "{$booking->user->name} ning {$booking->dacha->name} dachasiga bron so'rovi rad etildi.",
                    'data' => [
                        'booking_id' => $booking->id,
                        'dacha_name' => $booking->dacha->name,
                        'guest_name' => $booking->user->name,
                        'status' => 'cancelled',
                    ],
                ]);
            }

            // Send notification to customer via Telegram
            $this->telegram->sendBookingRejectedToCustomer($booking);

            return response()->json(['status' => 'rejected']);
        }

        return response()->json(['status' => 'invalid_action']);
    }
}
