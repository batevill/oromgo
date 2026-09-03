<?php

namespace App\Http\Controllers\Api\Owner;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Dacha;
use App\Services\BookingPriceCalculator;
use App\Services\OwnerReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class OwnerBookingController extends Controller
{
    /**
     * Dacha egasiga kelgan barcha bron so'rovlari
     */
    public function index(Request $request)
    {
        $query = Booking::whereHas('dacha', function ($q) use ($request) {
            if (!in_array($request->user()->role, ['admin', 'super_admin'])) {
                $q->where('user_id', $request->user()->id);
            }
        })->with(['dacha:id,name,region,district,address,currency', 'user:id,name,phone,avatar']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }

        if ($request->filled('dacha_id')) {
            $query->where('dacha_id', $request->dacha_id);
        }

        $bookings = $query->latest()->paginate(20);

        return response()->json($bookings);
    }

    /**
     * Dacha egasi hisobotlari va daromad statistikasi (Reports & Analytics)
     */
    public function reports(Request $request, OwnerReportService $reportService)
    {
        $filters = [
            'period' => $request->input('period', 'this_month'),
            'dacha_id' => $request->input('dacha_id'),
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
        ];

        $reportData = $reportService->getOwnerReports($request->user(), $filters);

        return response()->json($reportData);
    }

    /**
     * Dasturdan tashqari (Telegram, Telefon, Qo'lda) bronni tizimga kiritish
     */
    public function manualBooking(Request $request)
    {
        $validated = $request->validate([
            'dacha_id' => 'required|integer',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'total_price' => 'required|numeric|min:0',
            'currency' => 'nullable|in:USD,UZS',
            'source' => 'nullable|in:telegram,phone,manual,app',
            'customer_name' => 'nullable|string|max:150',
            'customer_phone' => 'nullable|string|max:50',
            'guests_count' => 'nullable|integer|min:1',
            'notes' => 'nullable|string|max:1000',
        ]);

        $dachaQuery = Dacha::where('id', $validated['dacha_id']);
        if (!in_array($request->user()->role, ['admin', 'super_admin'])) {
            $dachaQuery->where('user_id', $request->user()->id);
        }
        $dacha = $dachaQuery->firstOrFail();

        $start = Carbon::parse($validated['start_date'])->format('Y-m-d');
        $end = Carbon::parse($validated['end_date'])->format('Y-m-d');

        // To'qnashuvlarni tekshirish (faqat tasdiqlangan bronlar bilan)
        $isOverlapping = Booking::where('dacha_id', $dacha->id)
            ->whereIn('status', ['confirmed'])
            ->where(function ($q) use ($start, $end) {
                $q->where('start_date', '<=', $end)
                  ->where('end_date', '>=', $start);
            })
            ->exists();

        if ($isOverlapping) {
            return response()->json([
                'message' => 'Ushbu sanalar oralig\'ida dacha allaqachon boshqa tasdiqlangan bron bilan band.',
            ], 422);
        }

        $source = $validated['source'] ?? 'telegram';
        $booking = Booking::create([
            'dacha_id' => $dacha->id,
            'user_id' => $request->user()->id, // dacha egasi o'zi kiritmoqda
            'start_date' => $start,
            'end_date' => $end,
            'total_price' => $validated['total_price'],
            'currency' => $validated['currency'] ?? ($dacha->currency ?? 'USD'),
            'source' => $source,
            'customer_name' => $validated['customer_name'] ?? null,
            'customer_phone' => $validated['customer_phone'] ?? null,
            'guests_count' => $validated['guests_count'] ?? 1,
            'notes' => $validated['notes'] ?? ($source === 'telegram' ? 'Telegram orqali band qilindi' : 'Tashqi bron'),
            'status' => 'confirmed',
        ]);

        return response()->json([
            'message' => 'Tashqi bron muvaffaqiyatli tizimga qo\'shildi va hisobotga kiritildi!',
            'booking' => $booking->load('dacha:id,name,currency'),
        ], 201);
    }

    /**
     * Bron so'rovini tasdiqlash
     */
    public function confirm(Request $request, $id)
    {
        $booking = $this->findOwnerBooking($request, $id);

        if ($booking->status === 'confirmed') {
            return response()->json(['message' => 'Ushbu bron allaqachon tasdiqlangan.'], 400);
        }

        $booking->update(['status' => 'confirmed']);

        // Shu sanalardagi boshqa kutilayotgan (pending) so'rovlarni avtomatik bekor qilish
        $overlappingBookings = Booking::where('dacha_id', $booking->dacha_id)
            ->where('id', '!=', $booking->id)
            ->where('status', 'pending')
            ->where(function ($q) use ($booking) {
                $q->where('start_date', '<=', $booking->end_date)
                  ->where('end_date', '>=', $booking->start_date);
            })
            ->get();

        foreach ($overlappingBookings as $overlap) {
            $overlap->update(['status' => 'cancelled']);
            if ($overlap->user_id) {
                \App\Models\Notification::create([
                    'user_id' => $overlap->user_id,
                    'booking_id' => $overlap->id,
                    'type' => 'booking_cancelled',
                    'title' => 'Bron bekor qilindi ❌',
                    'message' => "\"{$booking->dacha->name}\" dachasiga so'rovingiz ushbu sanalar boshqa mijozga tasdiqlangani sababli bekor qilindi.",
                    'data' => [
                        'booking_id' => $overlap->id,
                        'dacha_name' => $booking->dacha->name,
                        'start_date' => $overlap->start_date->format('Y-m-d'),
                        'end_date' => $overlap->end_date->format('Y-m-d'),
                        'status' => 'cancelled',
                    ],
                ]);

                try {
                    app(\App\Services\TelegramService::class)->sendBookingRejectedToCustomer($overlap);
                } catch (\Throwable $e) {}
            }
        }

        // Mijozga sayt bildirishnomasi
        if ($booking->user_id && $booking->user_id != $request->user()->id) {
            \App\Models\Notification::create([
                'user_id' => $booking->user_id,
                'booking_id' => $booking->id,
                'type' => 'booking_confirmed',
                'title' => 'Broningiz tasdiqlandi! 🎉',
                'message' => "\"{$booking->dacha->name}\" dachasiga yuborgan bron so'rovingiz tasdiqlandi.",
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

            // Mijozga Telegram xabari
            try {
                app(\App\Services\TelegramService::class)->sendBookingConfirmedToCustomer($booking);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Telegram confirmation send failed: ' . $e->getMessage());
            }
        }

        return response()->json([
            'message' => 'Bron muvaffaqiyatli tasdiqlandi!',
            'booking' => $booking->fresh(['dacha', 'user']),
        ]);
    }

    /**
     * Bron so'rovini rad etish / bekor qilish
     */
    public function reject(Request $request, $id)
    {
        $booking = $this->findOwnerBooking($request, $id);

        $booking->update(['status' => 'cancelled']);

        if ($booking->user_id && $booking->user_id != $request->user()->id) {
            \App\Models\Notification::create([
                'user_id' => $booking->user_id,
                'booking_id' => $booking->id,
                'type' => 'booking_cancelled',
                'title' => 'Bron so\'rovi rad etildi ❌',
                'message' => "\"{$booking->dacha->name}\" dachasiga yuborgan bron so'rovingiz egasi tomonidan rad etildi.",
                'data' => [
                    'booking_id' => $booking->id,
                    'dacha_id' => $booking->dacha_id,
                    'dacha_name' => $booking->dacha->name,
                    'start_date' => $booking->start_date->format('Y-m-d'),
                    'end_date' => $booking->end_date->format('Y-m-d'),
                    'status' => 'cancelled',
                ],
            ]);

            try {
                app(\App\Services\TelegramService::class)->sendBookingRejectedToCustomer($booking);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Telegram rejection send failed: ' . $e->getMessage());
            }
        }

        return response()->json([
            'message' => 'Bron so\'rovi rad etildi / bekor qilindi.',
            'booking' => $booking->fresh(['dacha', 'user']),
        ]);
    }

    /**
     * Tashqi yoki yopilgan bronni butunlay o'chirish
     */
    public function destroy(Request $request, $id)
    {
        $booking = $this->findOwnerBooking($request, $id);
        $booking->delete();

        return response()->json([
            'message' => 'Bron muvaffaqiyatli o\'chirildi.',
        ]);
    }

    /**
     * Dacha egasi tomonidan sanalarni qo'lda "Band" deb yopib qo'yish (Block dates)
     */
    public function blockDates(Request $request, $dachaId)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'total_price' => 'nullable|numeric|min:0',
            'currency' => 'nullable|in:USD,UZS',
            'source' => 'nullable|string',
            'customer_name' => 'nullable|string|max:150',
            'customer_phone' => 'nullable|string|max:50',
            'reason' => 'nullable|string|max:255',
        ]);

        $dachaQuery = Dacha::where('id', $dachaId);
        if (!in_array($request->user()->role, ['admin', 'super_admin'])) {
            $dachaQuery->where('user_id', $request->user()->id);
        }
        $dacha = $dachaQuery->firstOrFail();

        $start = Carbon::parse($validated['start_date'])->format('Y-m-d');
        $end = Carbon::parse($validated['end_date'])->format('Y-m-d');

        // To'qnashuvlarni tekshirish
        $isOverlapping = Booking::where('dacha_id', $dacha->id)
            ->whereIn('status', ['confirmed'])
            ->where(function ($q) use ($start, $end) {
                $q->where('start_date', '<=', $end)
                  ->where('end_date', '>=', $start);
            })
            ->exists();

        if ($isOverlapping) {
            return response()->json([
                'message' => 'Ushbu sanalar allaqachon boshqa tasdiqlangan bron bilan band qilingan.',
            ], 422);
        }

        $price = $validated['total_price'] ?? 0;
        $booking = Booking::create([
            'dacha_id' => $dacha->id,
            'user_id' => $request->user()->id,
            'start_date' => $start,
            'end_date' => $end,
            'total_price' => $price,
            'currency' => $validated['currency'] ?? ($dacha->currency ?? 'USD'),
            'source' => $validated['source'] ?? ($price > 0 ? 'telegram' : 'manual'),
            'customer_name' => $validated['customer_name'] ?? null,
            'customer_phone' => $validated['customer_phone'] ?? null,
            'guests_count' => 0,
            'notes' => $validated['reason'] ?? 'Dacha egasi tomonidan yopilgan',
            'status' => 'confirmed',
        ]);

        return response()->json([
            'message' => 'Sanalar muvaffaqiyatli band qilib yopildi.',
            'booking' => $booking,
        ], 201);
    }

    /**
     * Dacha egasiga tegishli bronni topish
     */
    protected function findOwnerBooking(Request $request, $id): Booking
    {
        return Booking::where('id', $id)
            ->whereHas('dacha', function ($q) use ($request) {
                if (!in_array($request->user()->role, ['admin', 'super_admin'])) {
                    $q->where('user_id', $request->user()->id);
                }
            })
            ->firstOrFail();
    }
}

