<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Dacha;
use Illuminate\Http\Request;

class DachaController extends Controller
{
    /**
     * Barcha dachalarni qaytarish (Ochiq API)
     * Qidiruv va filtrlar ham shu yerda ishlaydi.
     */
    public function index(Request $request)
    {
        $query = Dacha::where('status', 'active');

        // Viloyat bo'yicha filtr
        if ($request->filled('region_id')) {
            $query->where('region_id', $request->region_id);
        } elseif ($request->filled('region')) {
            $query->where('region', $request->region);
        }

        // Tuman bo'yicha filtr
        if ($request->filled('district_id')) {
            $query->where('district_id', $request->district_id);
        } elseif ($request->filled('district')) {
            $query->where('district', $request->district);
        }

        // Mahalla / Qishloq bo'yicha filtr
        if ($request->filled('mahalla')) {
            $query->where('mahalla', $request->mahalla);
        }

        // Qidiruv so'zi (nomi, manzili yoki tavsifi bo'yicha)
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function($b) use ($q) {
                $b->where('name', 'like', "%{$q}%")
                  ->orWhere('description', 'like', "%{$q}%")
                  ->orWhere('region', 'like', "%{$q}%")
                  ->orWhere('district', 'like', "%{$q}%")
                  ->orWhere('mahalla', 'like', "%{$q}%")
                  ->orWhere('address', 'like', "%{$q}%");
            });
        }

        // Sig'imi bo'yicha filtr
        if ($request->filled('capacity')) {
            $query->where('capacity', '>=', $request->capacity);
        }

        // Valyuta va Narx bo'yicha aqlli (Dual-Currency konvertatsiyali) filtr
        $rate = (float) env('USD_TO_UZS_RATE', 13000); // 1 USD = 13,000 UZS
        $currency = $request->input('currency');
        $minPrice = $request->filled('min_price') ? (float) $request->min_price : null;
        $maxPrice = $request->filled('max_price') ? (float) $request->max_price : null;

        if ($minPrice !== null || $maxPrice !== null) {
            // Agar valyuta aniq kiritilmagan bo'lsa, kiritilgan narx miqdoridan avtomatik aniqlaymiz (< 5000 bo'lsa USD, aks holda UZS)
            $selectedCurrency = $currency ?: ($maxPrice && $maxPrice < 5000 ? 'USD' : ($minPrice && $minPrice < 5000 ? 'USD' : 'UZS'));

            $query->where(function ($q) use ($selectedCurrency, $minPrice, $maxPrice, $rate) {
                if ($selectedCurrency === 'USD') {
                    // 1. USD dagi dachalar uchun to'g'ridan-to'g'ri USD oraliq
                    $q->where(function ($sub) use ($minPrice, $maxPrice) {
                        $sub->where('currency', 'USD');
                        if ($minPrice !== null) $sub->where('weekday_price', '>=', $minPrice);
                        if ($maxPrice !== null) $sub->where('weekday_price', '<=', $maxPrice);
                    })
                    // 2. UZS dagi dachalar uchun so'mga aylantirilgan ekvivalent oraliq
                    ->orWhere(function ($sub) use ($minPrice, $maxPrice, $rate) {
                        $sub->where('currency', 'UZS');
                        if ($minPrice !== null) $sub->where('weekday_price', '>=', $minPrice * $rate);
                        if ($maxPrice !== null) $sub->where('weekday_price', '<=', $maxPrice * $rate);
                    });
                } else { // UZS
                    // 1. UZS dagi dachalar uchun to'g'ridan-to'g'ri UZS oraliq
                    $q->where(function ($sub) use ($minPrice, $maxPrice) {
                        $sub->where('currency', 'UZS');
                        if ($minPrice !== null) $sub->where('weekday_price', '>=', $minPrice);
                        if ($maxPrice !== null) $sub->where('weekday_price', '<=', $maxPrice);
                    })
                    // 2. USD dagi dachalar uchun dollarga aylantirilgan ekvivalent oraliq
                    ->orWhere(function ($sub) use ($minPrice, $maxPrice, $rate) {
                        $sub->where('currency', 'USD');
                        if ($minPrice !== null) $sub->where('weekday_price', '>=', $minPrice / $rate);
                        if ($maxPrice !== null) $sub->where('weekday_price', '<=', $maxPrice / $rate);
                    });
                }
            });
        }

        // Qulayliklar (Amenities) bo'yicha ko'p tanlovli (Multiple) filtr
        if ($request->filled('amenities') || $request->filled('amenity_ids')) {
            $amenityIds = $request->input('amenity_ids', $request->input('amenities'));
            if (is_string($amenityIds)) {
                $amenityIds = explode(',', $amenityIds);
            }
            $amenityIds = array_filter(array_map('intval', (array) $amenityIds));

            if (!empty($amenityIds)) {
                foreach ($amenityIds as $amenityId) {
                    $query->whereHas('amenities', function($q) use ($amenityId) {
                        $q->where('amenities.id', $amenityId);
                    });
                }
            }
        } elseif ($request->filled('amenity_id')) {
            $query->whereHas('amenities', function($q) use ($request) {
                $q->where('amenities.id', (int) $request->amenity_id);
            });
        }

        // Qisqa ma'lumotlarni qaytaramiz (rasmlari bilan birga)
        $dachas = $query->with('media')
                        ->latest()
                        ->paginate(15);

        return response()->json($dachas);
    }

    /**
     * Dacha haqida batafsil ma'lumot (Yopiq API, auth kerak)
     */
    public function show($id)
    {
        $dacha = Dacha::with([
            'owner:id,name,phone,avatar',
            'media',
            'amenities'
        ])->findOrFail($id);

        return response()->json($dacha);
    }

    /**
     * Barcha mavjud viloyat, tuman va mahallalar ierarxiyasi
     */
    public function locations()
    {
        $regions = \App\Models\Region::with(['districts' => function($q) {
            $q->orderBy('sort_order')->orderBy('name');
        }])->orderBy('sort_order')->get();

        $dachaMahallas = Dacha::where('status', 'active')
            ->whereNotNull('mahalla')
            ->select('region', 'district', 'mahalla')
            ->distinct()
            ->get();

        $hierarchy = [];
        foreach ($regions as $reg) {
            $hierarchy[$reg->name] = [];
            foreach ($reg->districts as $dist) {
                $mahallas = $dachaMahallas->where('region', $reg->name)
                    ->where('district', $dist->name)
                    ->pluck('mahalla')
                    ->values()
                    ->all();

                $hierarchy[$reg->name][$dist->name] = $mahallas;
            }
        }

        return response()->json($hierarchy);
    }
}
