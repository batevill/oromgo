<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Dacha;
use App\Models\Favorite;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    /**
     * Foydalanuvchining barcha sevimli dachalari ro'yxati
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $favorites = $user->favoriteDachas()
            ->with(['media', 'amenities'])
            ->where('status', 'active')
            ->latest('favorites.created_at')
            ->paginate(15);

        $favoriteIds = $user->favorites()->pluck('dacha_id')->toArray();

        return response()->json([
            'favorites' => $favorites,
            'favorite_ids' => $favoriteIds,
        ]);
    }

    /**
     * Dachani sevimlilarga qo'shish yoki o'chirish (Toggle)
     */
    public function toggle(Request $request, $dachaId)
    {
        $dacha = Dacha::findOrFail($dachaId);
        $userId = $request->user()->id;

        $favorite = Favorite::where('user_id', $userId)
            ->where('dacha_id', $dacha->id)
            ->first();

        if ($favorite) {
            $favorite->delete();
            return response()->json([
                'success' => true,
                'is_favorite' => false,
                'message' => 'Dacha sevimlilar ro\'yxatidan olib tashlandi.',
                'dacha_id' => (int) $dacha->id,
            ]);
        } else {
            Favorite::create([
                'user_id' => $userId,
                'dacha_id' => $dacha->id,
            ]);

            return response()->json([
                'success' => true,
                'is_favorite' => true,
                'message' => 'Dacha sevimlilar ro\'yxatiga saqlandi! ❤️',
                'dacha_id' => (int) $dacha->id,
            ], 200);
        }
    }
}
