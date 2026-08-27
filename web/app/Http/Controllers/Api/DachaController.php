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

        // Valyuta bo'yicha filtr
        if ($request->filled('currency')) {
            $query->where('currency', $request->currency);
        }

        // Ish kunlari narxi bo'yicha filtr (min va max)
        if ($request->filled('min_price')) {
            $query->where('weekday_price', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('weekday_price', '<=', $request->max_price);
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
