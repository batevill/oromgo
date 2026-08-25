<?php

namespace App\Services;

use App\Models\Dacha;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class BookingPriceCalculator
{
    /**
     * Dacha uchun sanalar oralig'idagi umumiy narxni va kunbay hisob-kitobni qaytaradi.
     */
    public static function calculate(Dacha $dacha, string $startDate, string $endDate): array
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->startOfDay();

        // Agar bir kunlik bo'lsa yoki tunash soni bo'yicha
        $isSingleDay = $start->equalTo($end);
        $period = $isSingleDay 
            ? [$start] 
            : CarbonPeriod::create($start, $end->copy()->subDay());

        $totalPrice = 0;
        $weekdaysCount = 0;
        $weekendDaysCount = 0;
        $breakdown = [];

        $weekdayPrice = (float) $dacha->weekday_price;
        $weekendPrice = (float) ($dacha->weekend_price ?: $dacha->weekday_price);

        foreach ($period as $date) {
            $isWeekend = $date->isSaturday() || $date->isSunday();
            $price = $isWeekend ? $weekendPrice : $weekdayPrice;

            if ($isWeekend) {
                $weekendDaysCount++;
            } else {
                $weekdaysCount++;
            }

            $totalPrice += $price;

            $breakdown[] = [
                'date' => $date->format('Y-m-d'),
                'day_name' => $date->translatedFormat('l'),
                'is_weekend' => $isWeekend,
                'price' => $price,
            ];
        }

        return [
            'start_date' => $start->format('Y-m-d'),
            'end_date' => $end->format('Y-m-d'),
            'total_nights' => count($breakdown),
            'weekdays_count' => $weekdaysCount,
            'weekend_days_count' => $weekendDaysCount,
            'weekday_price' => $weekdayPrice,
            'weekend_price' => $weekendPrice,
            'total_price' => $totalPrice,
            'currency' => $dacha->currency ?? 'USD',
            'daily_breakdown' => $breakdown,
        ];
    }
}
