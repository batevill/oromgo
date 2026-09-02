<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dacha;
use App\Models\Notification;
use Illuminate\Http\Request;

class AdminDachaController extends Controller
{
    /**
     * Moderatsiya va dachalarni boshqarish ro'yxati
     */
    public function index(Request $request)
    {
        $query = Dacha::with(['owner:id,name,phone,email,avatar', 'media', 'amenities'])
            ->latest();

        // Status bo'yicha filtr
        if ($request->filled('status') && in_array($request->status, ['pending', 'active', 'inactive'])) {
            $query->where('status', $request->status);
        }

        // Qidiruv
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($b) use ($q) {
                $b->where('name', 'like', "%{$q}%")
                  ->orWhere('region', 'like', "%{$q}%")
                  ->orWhere('district', 'like', "%{$q}%")
                  ->orWhereHas('owner', function ($uq) use ($q) {
                      $uq->where('name', 'like', "%{$q}%")
                         ->orWhere('phone', 'like', "%{$q}%");
                  });
            });
        }

        $dachas = $query->paginate(20);

        return response()->json($dachas);
    }

    /**
     * Dacha moderatsiya statusini yangilash (faol / nofaol / kutilmoqda)
     */
    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,active,inactive',
            'reason' => 'nullable|string|max:500',
        ]);

        $dacha = Dacha::with('owner')->findOrFail($id);
        $oldStatus = $dacha->status;
        $dacha->update(['status' => $validated['status']]);

        // Egasiga bildirishnoma (Notification) yuborish
        if ($dacha->user_id) {
            $statusTitles = [
                'active' => '🎉 E\'loningiz tasdiqlandi va faollashtirildi!',
                'inactive' => '⏸️ E\'loningiz vaqtincha to\'xtatildi / nofaol qilindi',
                'pending' => '⏳ E\'loningiz qayta moderatsiyaga yuborildi',
            ];

            $statusMessages = [
                'active' => "Tabriklaymiz! \"{$dacha->name}\" dachangiz moderator tomonidan tasdiqlandi va hozirda barcha mijozlarga ko'rinmoqda.",
                'inactive' => "Sizning \"{$dacha->name}\" dachangiz administrator tomonidan nofaol holatga o'tkazildi." . ($request->filled('reason') ? " Sabab: " . $request->reason : ""),
                'pending' => "Sizning \"{$dacha->name}\" dachangiz tekshiruv uchun kutilmoqda holatiga o'tkazildi.",
            ];

            Notification::create([
                'user_id' => $dacha->user_id,
                'title' => $statusTitles[$validated['status']] ?? 'Dacha holati yangilandi',
                'message' => $statusMessages[$validated['status']] ?? 'Dachangiz holati administrator tomonidan o\'zgartirildi.',
                'type' => $validated['status'] === 'active' ? 'system' : 'warning',
                'is_read' => false,
            ]);
        }

        return response()->json([
            'message' => 'Dacha statusi muvaffaqiyatli yangilandi.',
            'dacha' => $dacha
        ]);
    }

    /**
     * Admin statistikasi (Jami, Moderatsiyada, Faol, Nofaol)
     */
    public function stats()
    {
        $total = Dacha::count();
        $pending = Dacha::where('status', 'pending')->count();
        $active = Dacha::where('status', 'active')->count();
        $inactive = Dacha::where('status', 'inactive')->count();

        return response()->json([
            'total' => $total,
            'pending' => $pending,
            'active' => $active,
            'inactive' => $inactive,
        ]);
    }

    /**
     * Dacha e'lonini o'chirish
     */
    public function destroy($id)
    {
        $dacha = Dacha::findOrFail($id);
        $dacha->delete();

        return response()->json([
            'message' => 'Dacha e\'loni butunlay o\'chirildi.'
        ]);
    }
}
