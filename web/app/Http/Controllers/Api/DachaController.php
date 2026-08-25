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
        if ($request->filled('region')) {
            $query->where('region', $request->region);
        }

        // Tuman bo'yicha filtr
        if ($request->filled('district')) {
            $query->where('district', $request->district);
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
}
