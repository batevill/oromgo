<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Dacha;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;

class OwnerReportService
{
    /**
     * Dacha egasining hisobotlari va daromad tahlilini shakllantirish
     */
    public function getOwnerReports(User $owner, array $filters = []): array
    {
        // 1. Dacha egasiga tegishli dachalarni aniqlash
        $dachasQuery = Dacha::query();
        if (!in_array($owner->role, ['admin', 'super_admin'])) {
            $dachasQuery->where('user_id', $owner->id);
        }
        $ownerDachas = $dachasQuery->get(['id', 'name', 'currency', 'weekday_price', 'weekend_price']);
        $dachaIds = $ownerDachas->pluck('id')->toArray();

        if (empty($dachaIds)) {
            return $this->getEmptyReport();
        }

        // 2. Sanalar oralig'ini aniqlash (Filter)
        $period = $filters['period'] ?? 'this_month';
        $dachaFilterId = $filters['dacha_id'] ?? null;

        $now = Carbon::now();
        switch ($period) {
            case 'last_month':
                $startDate = $now->copy()->subMonth()->startOfMonth();
                $endDate = $now->copy()->subMonth()->endOfMonth();
                $periodLabel = $startDate->translatedFormat('F Y');
                break;
            case 'this_year':
                $startDate = $now->copy()->startOfYear();
                $endDate = $now->copy()->endOfYear();
                $periodLabel = $now->format('Y') . " - yil";
                break;
            case 'all':
                $startDate = Carbon::create(2020, 1, 1);
                $endDate = $now->copy()->addYear()->endOfYear();
                $periodLabel = "Barcha davr";
                break;
            case 'custom':
                $startDate = !empty($filters['start_date']) ? Carbon::parse($filters['start_date'])->startOfDay() : $now->copy()->startOfMonth();
                $endDate = !empty($filters['end_date']) ? Carbon::parse($filters['end_date'])->endOfDay() : $now->copy()->endOfMonth();
                $periodLabel = $startDate->format('d.m.Y') . " - " . $endDate->format('d.m.Y');
                break;
            case 'this_month':
            default:
                $startDate = $now->copy()->startOfMonth();
                $endDate = $now->copy()->endOfMonth();
                $periodLabel = $now->translatedFormat('F Y');
                break;
        }

        $targetDachaIds = $dachaFilterId && in_array($dachaFilterId, $dachaIds) ? [$dachaFilterId] : $dachaIds;

        // 3. Asosiy Booking so'rovlari
        $bookingsQuery = Booking::whereIn('dacha_id', $targetDachaIds)
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('start_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                  ->orWhereBetween('end_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                  ->orWhere(function ($sub) use ($startDate, $endDate) {
                      $sub->where('start_date', '<=', $startDate->format('Y-m-d'))
                          ->where('end_date', '>=', $endDate->format('Y-m-d'));
                  });
            });

        $allBookings = (clone $bookingsQuery)->with(['dacha:id,name,currency', 'user:id,name,phone'])->get();

        // 4. Faqat tasdiqlangan (daromad keltiruvchi) bronlar
        $confirmedBookings = $allBookings->filter(fn($b) => in_array($b->status, ['confirmed', 'completed']));

        // Jami daromad (valyutalar bo'yicha)
        $incomeUZS = 0;
        $incomeUSD = 0;
        foreach ($confirmedBookings as $b) {
            if ($b->currency === 'UZS') {
                $incomeUZS += (float) $b->total_price;
            } else {
                $incomeUSD += (float) $b->total_price;
            }
        }

        // 5. Band kunlar soni va bandlik darajasi (Occupancy)
        $bookedDatesSet = [];
        foreach ($confirmedBookings as $b) {
            $bStart = Carbon::parse($b->start_date);
            $bEnd = Carbon::parse($b->end_date);
            
            // Faqat tanlangan davr ichiga tushadigan kunlarni qamrab olamiz
            $effStart = $bStart->max($startDate);
            $effEnd = $bEnd->min($endDate);

            if ($effStart->lte($effEnd)) {
                $periodRange = $effStart->equalTo($effEnd) ? [$effStart] : CarbonPeriod::create($effStart, $effEnd->copy()->subDay());
                foreach ($periodRange as $d) {
                    $bookedDatesSet[$b->dacha_id . '_' . $d->format('Y-m-d')] = true;
                }
            }
        }
        $totalBookedDays = count($bookedDatesSet);

        $daysInPeriod = max(1, $startDate->diffInDays($endDate) + 1);
        $totalPossibleDays = count($targetDachaIds) * $daysInPeriod;
        $occupancyRate = $totalPossibleDays > 0 ? round(($totalBookedDays / $totalPossibleDays) * 100, 1) : 0;

        // 6. Manbalar bo'yicha taqsimot (Source breakdown)
        $sources = [
            'telegram' => ['label' => 'Telegram Bot & Kanal', 'icon' => '📱', 'count' => 0, 'income_uzs' => 0, 'income_usd' => 0],
            'app'      => ['label' => 'Oromgo Dasturi',       'icon' => '🌟', 'count' => 0, 'income_uzs' => 0, 'income_usd' => 0],
            'phone'    => ['label' => 'Telefon / Qo\'ng\'iroq', 'icon' => '📞', 'count' => 0, 'income_uzs' => 0, 'income_usd' => 0],
            'manual'   => ['label' => 'Qo\'lda / Boshqa',     'icon' => '📝', 'count' => 0, 'income_uzs' => 0, 'income_usd' => 0],
        ];

        foreach ($confirmedBookings as $b) {
            $src = $b->source ?: 'app';
            if (!isset($sources[$src])) {
                $src = 'manual';
            }
            $sources[$src]['count']++;
            if ($b->currency === 'UZS') {
                $sources[$src]['income_uzs'] += (float) $b->total_price;
            } else {
                $sources[$src]['income_usd'] += (float) $b->total_price;
            }
        }

        // 7. Oylik trend (So'nggi 6 oy)
        $monthlyTrend = [];
        for ($i = 5; $i >= 0; $i--) {
            $mStart = $now->copy()->subMonths($i)->startOfMonth();
            $mEnd = $now->copy()->subMonths($i)->endOfMonth();
            $mKey = $mStart->format('Y-m');
            $mName = $mStart->translatedFormat('M Y');

            $mBookings = Booking::whereIn('dacha_id', $targetDachaIds)
                ->whereIn('status', ['confirmed', 'completed'])
                ->whereBetween('start_date', [$mStart->format('Y-m-d'), $mEnd->format('Y-m-d')])
                ->get();

            $mIncomeUZS = $mBookings->where('currency', 'UZS')->sum('total_price');
            $mIncomeUSD = $mBookings->where('currency', 'USD')->sum('total_price');

            $monthlyTrend[] = [
                'month_key' => $mKey,
                'month_name' => $mName,
                'bookings_count' => $mBookings->count(),
                'income_uzs' => (float) $mIncomeUZS,
                'income_usd' => (float) $mIncomeUSD,
            ];
        }

        // 8. Dacha kesimida daromad va statistika
        $dachasBreakdown = [];
        foreach ($ownerDachas as $dacha) {
            if ($dachaFilterId && $dacha->id != $dachaFilterId) {
                continue;
            }

            $dBookings = $confirmedBookings->where('dacha_id', $dacha->id);
            $dIncomeUZS = $dBookings->where('currency', 'UZS')->sum('total_price');
            $dIncomeUSD = $dBookings->where('currency', 'USD')->sum('total_price');

            $dachasBreakdown[] = [
                'id' => $dacha->id,
                'name' => $dacha->name,
                'currency' => $dacha->currency ?? 'USD',
                'bookings_count' => $dBookings->count(),
                'income_uzs' => (float) $dIncomeUZS,
                'income_usd' => (float) $dIncomeUSD,
            ];
        }

        // 9. So'nggi bronlar ro'yxati (oxirgi 10 ta)
        $recentBookings = $allBookings->sortByDesc('created_at')->take(10)->values()->map(function ($b) {
            return [
                'id' => $b->id,
                'dacha_id' => $b->dacha_id,
                'dacha_name' => $b->dacha ? $b->dacha->name : 'Dacha',
                'guest_name' => $b->guest_name,
                'guest_phone' => $b->guest_phone,
                'start_date' => $b->start_date ? $b->start_date->format('Y-m-d') : '',
                'end_date' => $b->end_date ? $b->end_date->format('Y-m-d') : '',
                'total_price' => (float) $b->total_price,
                'currency' => $b->currency,
                'source' => $b->source ?: 'app',
                'source_label' => $b->source_label,
                'status' => $b->status,
                'notes' => $b->notes,
                'created_at' => $b->created_at ? $b->created_at->format('Y-m-d H:i') : '',
            ];
        });

        return [
            'period' => $period,
            'period_label' => $periodLabel,
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'summary' => [
                'total_income_uzs' => $incomeUZS,
                'total_income_usd' => $incomeUSD,
                'total_bookings' => $allBookings->count(),
                'confirmed_bookings' => $confirmedBookings->count(),
                'pending_bookings' => $allBookings->where('status', 'pending')->count(),
                'cancelled_bookings' => $allBookings->where('status', 'cancelled')->count(),
                'total_booked_days' => $totalBookedDays,
                'occupancy_rate' => $occupancyRate,
            ],
            'sources' => array_values($sources),
            'monthly_trend' => $monthlyTrend,
            'dachas_breakdown' => $dachasBreakdown,
            'recent_bookings' => $recentBookings,
            'owner_dachas' => $ownerDachas,
        ];
    }

    protected function getEmptyReport(): array
    {
        return [
            'period' => 'this_month',
            'period_label' => Carbon::now()->translatedFormat('F Y'),
            'start_date' => Carbon::now()->startOfMonth()->format('Y-m-d'),
            'end_date' => Carbon::now()->endOfMonth()->format('Y-m-d'),
            'summary' => [
                'total_income_uzs' => 0,
                'total_income_usd' => 0,
                'total_bookings' => 0,
                'confirmed_bookings' => 0,
                'pending_bookings' => 0,
                'cancelled_bookings' => 0,
                'total_booked_days' => 0,
                'occupancy_rate' => 0,
            ],
            'sources' => [],
            'monthly_trend' => [],
            'dachas_breakdown' => [],
            'recent_bookings' => [],
            'owner_dachas' => [],
        ];
    }
}
