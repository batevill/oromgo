<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Dacha;
use App\Services\BookingPriceCalculator;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    /**
     * Sanalar bo'yicha narxni avtomatik hisoblab berish (Ochiq API)
     */
    public function calculatePrice(Request $request, $dachaId)
    {
        $request->validate([
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $dacha = Dacha::findOrFail($dachaId);

        $calculation = BookingPriceCalculator::calculate(
            $dacha,
            $request->start_date,
            $request->end_date
        );

        return response()->json($calculation);
    }

    /**
     * Dachaning band qilingan sanalari (Kalendar uchun ochiq API)
     */
    public function calendar(Request $request, $dachaId)
    {
        $dacha = Dacha::findOrFail($dachaId);

        $from = $request->input('from', Carbon::today()->format('Y-m-d'));
        $to = $request->input('to', Carbon::today()->addMonths(6)->format('Y-m-d'));

        // Faqat tasdiqlangan (va kutilayotgan) bandliklarni olamiz
        $bookings = Booking::where('dacha_id', $dacha->id)
            ->whereIn('status', ['confirmed', 'pending'])
            ->where(function ($query) use ($from, $to) {
                $query->whereBetween('start_date', [$from, $to])
                      ->orWhereBetween('end_date', [$from, $to])
                      ->orWhere(function ($q) use ($from, $to) {
                          $q->where('start_date', '<=', $from)
                            ->where('end_date', '>=', $to);
                      });
            })
            ->get(['id', 'start_date', 'end_date', 'status']);

        // Har bir band qilingan kunlar ro'yxatini shakllantiramiz
        $bookedDates = [];
        foreach ($bookings as $booking) {
            $period = CarbonPeriod::create($booking->start_date, $booking->end_date);
            foreach ($period as $date) {
                $bookedDates[] = [
                    'date' => $date->format('Y-m-d'),
                    'status' => $booking->status,
                ];
            }
        }

        return response()->json([
            'dacha_id' => $dacha->id,
            'booked_ranges' => $bookings,
            'booked_dates' => array_values(array_unique($bookedDates, SORT_REGULAR)),
        ]);
    }

    /**
     * Dacha uchun yangi bron (band qilish) yaratish (Auth talab qilinadi)
     */
    public function store(Request $request, $dachaId)
    {
        $validated = $request->validate([
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'guests_count' => 'nullable|integer|min:1',
            'notes' => 'nullable|string|max:1000',
        ]);

        $dacha = Dacha::findOrFail($dachaId);

        $start = Carbon::parse($validated['start_date'])->format('Y-m-d');
        $end = Carbon::parse($validated['end_date'])->format('Y-m-d');

        // Tanlangan sanalar boshqa bandliklar bilan to'qnash kelmasligini tekshiramiz
        $isOverlapping = Booking::where('dacha_id', $dacha->id)
            ->whereIn('status', ['confirmed', 'pending'])
            ->where(function ($query) use ($start, $end) {
                $query->where(function ($q) use ($start, $end) {
                    $q->where('start_date', '<=', $end)
                      ->where('end_date', '>=', $start);
                });
            })
            ->exists();

        if ($isOverlapping) {
            return response()->json([
                'message' => 'Kechirasiz, tanlangan sanalar oralig\'ida dacha allaqachon band qilingan.',
            ], 422);
        }

        // Narxni avtomatik hisoblash
        $calculation = BookingPriceCalculator::calculate($dacha, $start, $end);

        $booking = Booking::create([
            'dacha_id' => $dacha->id,
            'user_id' => $request->user()->id,
            'start_date' => $start,
            'end_date' => $end,
            'total_price' => $calculation['total_price'],
            'currency' => $dacha->currency ?? 'USD',
            'guests_count' => $validated['guests_count'] ?? 1,
            'notes' => $validated['notes'] ?? null,
            'status' => 'pending',
        ]);

        // 1. Dacha egasiga sayt bildirishnomasi
        \App\Models\Notification::create([
            'user_id' => $dacha->user_id,
            'booking_id' => $booking->id,
            'type' => 'booking_created',
            'title' => 'Yangi bron so\'rovi tushdi 🔔',
            'message' => "{$request->user()->name} sizning \"{$dacha->name}\" dachangizni bron qildi.",
            'data' => [
                'booking_id' => $booking->id,
                'dacha_id' => $dacha->id,
                'dacha_name' => $dacha->name,
                'guest_name' => $request->user()->name,
                'guest_phone' => $request->user()->phone,
                'start_date' => $start,
                'end_date' => $end,
                'guests_count' => $booking->guests_count,
                'total_price' => $booking->total_price,
                'currency' => $booking->currency,
                'notes' => $booking->notes,
                'status' => 'pending',
            ],
        ]);

        // 2. Mijozning o'ziga sayt bildirishnomasi
        \App\Models\Notification::create([
            'user_id' => $request->user()->id,
            'booking_id' => $booking->id,
            'type' => 'booking_created',
            'title' => 'Bron so\'rovingiz yuborildi ⏳',
            'message' => "\"{$dacha->name}\" dachasiga bron so'rovingiz qabul qilindi. Dacha egasi tasdiqlashini kuting.",
            'data' => [
                'booking_id' => $booking->id,
                'dacha_id' => $dacha->id,
                'dacha_name' => $dacha->name,
                'start_date' => $start,
                'end_date' => $end,
                'total_price' => $booking->total_price,
                'currency' => $booking->currency,
                'status' => 'pending',
            ],
        ]);

        // 3. Dacha egasiga Telegram orqali jonli xabar va [Tasdiqlash] / [Rad etish] tugmalari
        try {
            app(\App\Services\TelegramService::class)->sendBookingRequestToOwner($booking);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Telegram notification error: ' . $e->getMessage());
        }

        return response()->json([
            'message' => 'Bron so\'rovingiz muvaffaqiyatli yuborildi. Dacha egasi tasdiqlashini kuting.',
            'booking' => $booking->load('dacha:id,name,region,district,address'),
            'calculation' => $calculation,
        ], 201);
    }

    /**
     * Foydalanuvchining barcha qilgan bronlari ro'yxati
     */
    public function myBookings(Request $request)
    {
        $bookings = Booking::where('user_id', $request->user()->id)
            ->with(['dacha' => function ($query) {
                $query->with('media');
            }])
            ->latest()
            ->paginate(15);

        return response()->json($bookings);
    }

    /**
     * Foydalanuvchi o'z bronini bekor qilishi
     */
    public function cancel(Request $request, $id)
    {
        $booking = Booking::with('dacha.user')->where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        if ($booking->status === 'cancelled') {
            return response()->json(['message' => 'Ushbu bron allaqachon bekor qilingan.'], 400);
        }

        $booking->update(['status' => 'cancelled']);

        // Egasi uchun bildirishnoma yaratish
        if ($booking->dacha && $booking->dacha->user_id) {
            \App\Models\Notification::create([
                'user_id' => $booking->dacha->user_id,
                'booking_id' => $booking->id,
                'type' => 'booking_cancelled',
                'title' => 'Bron mijoz tomonidan bekor qilindi ℹ️',
                'message' => "{$request->user()->name} \"{$booking->dacha->name}\" dachasidagi bronini bekor qildi.",
                'data' => [
                    'booking_id' => $booking->id,
                    'dacha_name' => $booking->dacha->name,
                    'guest_name' => $request->user()->name,
                    'start_date' => $booking->start_date ? $booking->start_date->format('Y-m-d') : '',
                    'end_date' => $booking->end_date ? $booking->end_date->format('Y-m-d') : '',
                    'status' => 'cancelled',
                ],
            ]);

            try {
                app(\App\Services\TelegramService::class)->sendBookingCancelledToOwner($booking);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Telegram notification error: ' . $e->getMessage());
            }
        }

        return response()->json([
            'message' => 'Bron muvaffaqiyatli bekor qilindi.',
            'booking' => $booking,
        ]);
    }
}
