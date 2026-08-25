<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Dacha;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /**
     * Dachaning barcha sharhlari va umumiy reytingi
     */
    public function index($dachaId)
    {
        $dacha = Dacha::findOrFail($dachaId);

        $reviews = Review::where('dacha_id', $dacha->id)
            ->with('user:id,name,avatar')
            ->latest()
            ->get();

        $avgRating = $reviews->count() > 0 ? round($reviews->avg('rating'), 1) : 5.0;

        return response()->json([
            'dacha_id' => $dacha->id,
            'avg_rating' => $avgRating,
            'total_reviews' => $reviews->count(),
            'reviews' => $reviews,
        ]);
    }

    /**
     * Yangi sharh va baho qoldirish (Auth talab qilinadi)
     */
    public function store(Request $request, $dachaId)
    {
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'cleanliness_rating' => 'nullable|integer|min:1|max:5',
            'comfort_rating' => 'nullable|integer|min:1|max:5',
            'comment' => 'required|string|min:3|max:1000',
            'booking_id' => 'nullable|exists:bookings,id',
        ]);

        $dacha = Dacha::findOrFail($dachaId);

        $review = Review::create([
            'dacha_id' => $dacha->id,
            'user_id' => $request->user()->id,
            'booking_id' => $validated['booking_id'] ?? null,
            'rating' => $validated['rating'],
            'cleanliness_rating' => $validated['cleanliness_rating'] ?? null,
            'comfort_rating' => $validated['comfort_rating'] ?? null,
            'comment' => $validated['comment'],
        ]);

        return response()->json([
            'message' => 'Sharhingiz muvaffaqiyatli qoldirildi! Fikringiz uchun rahmat.',
            'review' => $review->load('user:id,name,avatar'),
        ], 201);
    }
}
