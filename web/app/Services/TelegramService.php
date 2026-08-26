<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    protected ?string $botToken;
    protected string $apiUrl;

    public function __construct()
    {
        $this->botToken = config('services.telegram.client_secret') ?: env('TELEGRAM_BOT_TOKEN');
        $this->apiUrl = "https://api.telegram.org/bot{$this->botToken}";
    }

    /**
     * Check if Telegram Bot token is configured
     */
    public function isConfigured(): bool
    {
        return !empty($this->botToken);
    }

    /**
     * Send basic message to a chat/user ID
     */
    public function sendMessage($chatId, string $text, ?array $inlineKeyboard = null, string $parseMode = 'HTML')
    {
        if (!$this->isConfigured() || empty($chatId)) {
            Log::warning("TelegramService: Bot token or chat_id is missing.", ['chat_id' => $chatId]);
            return null;
        }

        $payload = [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => $parseMode,
        ];

        if ($inlineKeyboard) {
            $payload['reply_markup'] = json_encode([
                'inline_keyboard' => $inlineKeyboard,
            ]);
        }

        try {
            $response = Http::timeout(10)->post("{$this->apiUrl}/sendMessage", $payload);
            
            if (!$response->successful()) {
                Log::error("TelegramService sendMessage failed", [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'chat_id' => $chatId,
                ]);
            }

            return $response->json();
        } catch (\Throwable $e) {
            Log::error("TelegramService Exception on sendMessage: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Edit message text (for updating booking confirmation status in-place)
     */
    public function editMessageText($chatId, $messageId, string $text, ?array $inlineKeyboard = null, string $parseMode = 'HTML')
    {
        if (!$this->isConfigured() || empty($chatId) || empty($messageId)) {
            return null;
        }

        $payload = [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => $text,
            'parse_mode' => $parseMode,
        ];

        if ($inlineKeyboard !== null) {
            $payload['reply_markup'] = json_encode([
                'inline_keyboard' => $inlineKeyboard,
            ]);
        }

        try {
            $response = Http::timeout(10)->post("{$this->apiUrl}/editMessageText", $payload);
            return $response->json();
        } catch (\Throwable $e) {
            Log::error("TelegramService editMessageText Exception: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Answer callback query (pop-up toast or notification inside Telegram)
     */
    public function answerCallbackQuery(string $callbackQueryId, ?string $text = null, bool $showAlert = false)
    {
        if (!$this->isConfigured()) return null;

        try {
            return Http::timeout(10)->post("{$this->apiUrl}/answerCallbackQuery", [
                'callback_query_id' => $callbackQueryId,
                'text' => $text,
                'show_alert' => $showAlert,
            ])->json();
        } catch (\Throwable $e) {
            Log::error("TelegramService answerCallbackQuery Exception: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Send new booking request to Dacha Owner with inline Action Buttons
     */
    public function sendBookingRequestToOwner(Booking $booking)
    {
        $booking->loadMissing(['dacha.user', 'user']);
        $owner = $booking->dacha->user ?? null;

        if (!$owner || empty($owner->telegram_id)) {
            Log::info("Owner has no telegram_id linked, skipping telegram notification.", [
                'booking_id' => $booking->id,
                'owner_id' => $owner->id ?? null,
            ]);
            return null;
        }

        $customerName = htmlspecialchars($booking->user->name ?? 'Mijoz');
        $customerPhone = htmlspecialchars($booking->user->phone ?? 'Ko\'rsatilmagan');
        $dachaName = htmlspecialchars($booking->dacha->name ?? 'Dacha');
        $startDate = $booking->start_date ? $booking->start_date->format('Y-m-d') : '';
        $endDate = $booking->end_date ? $booking->end_date->format('Y-m-d') : '';
        $currencySymbol = $booking->currency === 'UZS' ? "so'm" : "$";
        $totalPrice = number_format($booking->total_price, 0, '.', ' ');
        $notes = $booking->notes ? "\n💬 <b>Izoh:</b> " . htmlspecialchars($booking->notes) : '';

        $text = "🔔 <b>YANGI BRON SO'ROVI TUSHDI!</b>\n\n"
              . "🏡 <b>Dacha:</b> {$dachaName}\n"
              . "👤 <b>Mijoz:</b> {$customerName}\n"
              . "📞 <b>Telefon:</b> {$customerPhone}\n"
              . "📅 <b>Sanalar:</b> <code>{$startDate}</code> dan <code>{$endDate}</code> gacha\n"
              . "👥 <b>Mehmonlar:</b> {$booking->guests_count} kishi\n"
              . "💰 <b>Jami summa:</b> <b>{$totalPrice} {$currencySymbol}</b>"
              . $notes . "\n\n"
              . "<i>Bronni tasdiqlaysizmi yoki rad etasizmi?</i>";

        $keyboard = [
            [
                [
                    'text' => '✅ Tasdiqlash',
                    'callback_data' => "confirm_booking:{$booking->id}",
                ],
                [
                    'text' => '❌ Rad etish',
                    'callback_data' => "reject_booking:{$booking->id}",
                ]
            ]
        ];

        return $this->sendMessage($owner->telegram_id, $text, $keyboard);
    }

    /**
     * Send confirmation notification to customer
     */
    public function sendBookingConfirmedToCustomer(Booking $booking)
    {
        $booking->loadMissing(['dacha', 'user']);
        $customer = $booking->user;

        if (!$customer || empty($customer->telegram_id)) {
            return null;
        }

        $dachaName = htmlspecialchars($booking->dacha->name ?? 'Dacha');
        $startDate = $booking->start_date ? $booking->start_date->format('Y-m-d') : '';
        $endDate = $booking->end_date ? $booking->end_date->format('Y-m-d') : '';
        $currencySymbol = $booking->currency === 'UZS' ? "so'm" : "$";
        $totalPrice = number_format($booking->total_price, 0, '.', ' ');

        $text = "🎉 <b>BRONINGIZ TASDIQLANDI!</b>\n\n"
              . "Hurmatli <b>" . htmlspecialchars($customer->name) . "</b>,\n"
              . "Sizning <b>{$dachaName}</b> dachasiga yuborgan bron so'rovingiz dacha egasi tomonidan tasdiqlandi.\n\n"
              . "📅 <b>Sanalar:</b> <code>{$startDate}</code> — <code>{$endDate}</code>\n"
              . "💰 <b>Summa:</b> {$totalPrice} {$currencySymbol}\n"
              . "📍 <b>Manzil:</b> " . htmlspecialchars($booking->dacha->address ?? $booking->dacha->district ?? '') . "\n\n"
              . "Oromgo bilan maroqli dam oling! 🌿";

        return $this->sendMessage($customer->telegram_id, $text);
    }

    /**
     * Send rejection notification to customer
     */
    public function sendBookingRejectedToCustomer(Booking $booking)
    {
        $booking->loadMissing(['dacha', 'user']);
        $customer = $booking->user;

        if (!$customer || empty($customer->telegram_id)) {
            return null;
        }

        $dachaName = htmlspecialchars($booking->dacha->name ?? 'Dacha');
        $startDate = $booking->start_date ? $booking->start_date->format('Y-m-d') : '';
        $endDate = $booking->end_date ? $booking->end_date->format('Y-m-d') : '';

        $text = "⚠️ <b>BRON BEKOR QILINDI / RAD ETILDI</b>\n\n"
              . "Hurmatli <b>" . htmlspecialchars($customer->name) . "</b>,\n"
              . "Afsuski, sizning <b>{$dachaName}</b> dachasiga <code>{$startDate}</code> — <code>{$endDate}</code> sanalari uchun qilgan bron so'rovingiz tasdiqlanmadi.\n\n"
              . "Iltimos, boshqa bo'sh sanalarni yoki boshqa dachalarni tanlab ko'ring: /dachas";

        return $this->sendMessage($customer->telegram_id, $text);
    }

    /**
     * Send cancellation notice to Dacha Owner when customer cancels
     */
    public function sendBookingCancelledToOwner(Booking $booking)
    {
        $booking->loadMissing(['dacha.user', 'user']);
        $owner = $booking->dacha->user ?? null;

        if (!$owner || empty($owner->telegram_id)) {
            return null;
        }

        $dachaName = htmlspecialchars($booking->dacha->name ?? 'Dacha');
        $customerName = htmlspecialchars($booking->user->name ?? 'Mijoz');
        $startDate = $booking->start_date ? $booking->start_date->format('Y-m-d') : '';
        $endDate = $booking->end_date ? $booking->end_date->format('Y-m-d') : '';

        $text = "ℹ️ <b>BRON MIJOZ TOMONIDAN BEKOR QILINDI</b>\n\n"
              . "🏡 <b>Dacha:</b> {$dachaName}\n"
              . "👤 <b>Mijoz:</b> {$customerName}\n"
              . "📅 <b>Sanalar:</b> <code>{$startDate}</code> — <code>{$endDate}</code>\n\n"
              . "Ushbu sanalar yana taqvimda bo'sh deb belgilandi.";

        return $this->sendMessage($owner->telegram_id, $text);
    }

    /**
     * Send reminder (1 day before booking)
     */
    public function sendBookingReminder(Booking $booking, string $recipientType = 'customer')
    {
        $booking->loadMissing(['dacha.user', 'user']);
        $dachaName = htmlspecialchars($booking->dacha->name ?? 'Dacha');
        $startDate = $booking->start_date ? $booking->start_date->format('Y-m-d') : '';

        if ($recipientType === 'customer') {
            $customer = $booking->user;
            if (!$customer || empty($customer->telegram_id)) return null;

            $text = "⏰ <b>ESLATMA: ERTAGA DAM OLISH KUNINGIZ!</b>\n\n"
                  . "Hurmatli <b>" . htmlspecialchars($customer->name) . "</b>,\n"
                  . "Ertaga (<code>{$startDate}</code>) sizning <b>{$dachaName}</b> dachasiga kirish kuningiz boshlanadi.\n\n"
                  . "📍 <b>Manzil:</b> " . htmlspecialchars($booking->dacha->address ?? '') . "\n"
                  . "📞 <b>Dacha egasi tel:</b> " . htmlspecialchars($booking->dacha->user->phone ?? 'Ko\'rsatilmagan') . "\n\n"
                  . "Sizga yoqimli va mazmunli hordiq tilaymiz! 🏔️✨";

            return $this->sendMessage($customer->telegram_id, $text);
        } else {
            $owner = $booking->dacha->user ?? null;
            if (!$owner || empty($owner->telegram_id)) return null;

            $customerName = htmlspecialchars($booking->user->name ?? 'Mijoz');
            $customerPhone = htmlspecialchars($booking->user->phone ?? 'Noma\'lum');

            $text = "⏰ <b>ESLATMA: ERTAGA MEHMONLAR KELADI!</b>\n\n"
                  . "Hurmatli dacha egasi,\n"
                  . "Ertaga (<code>{$startDate}</code>) <b>{$dachaName}</b> dachangizga mehmonlar tashrif buyuradi.\n\n"
                  . "👤 <b>Mijoz:</b> {$customerName}\n"
                  . "📞 <b>Telefon:</b> {$customerPhone}\n"
                  . "👥 <b>Mehmonlar soni:</b> {$booking->guests_count} kishi\n\n"
                  . "Dachani mehmonlar qabuliga tayyorlashni unutmang! 🏡";

            return $this->sendMessage($owner->telegram_id, $text);
        }
    }

    /**
     * Get Bot Info or Bot username
     */
    public function getBotUsername(): ?string
    {
        if (!$this->isConfigured()) return null;

        try {
            $res = Http::timeout(5)->get("{$this->apiUrl}/getMe")->json();
            return $res['result']['username'] ?? null;
        } catch (\Throwable $e) {
            return null;
        }
    }
}
