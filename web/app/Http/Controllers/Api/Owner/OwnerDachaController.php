<?php

namespace App\Http\Controllers\Api\Owner;

use App\Http\Controllers\Controller;
use App\Models\Dacha;
use App\Models\DachaMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class OwnerDachaController extends Controller
{
    /**
     * Dacha egasining barcha e'lonlari ro'yxati
     */
    public function index(Request $request)
    {
        $dachas = Dacha::where('user_id', $request->user()->id)
            ->with(['media', 'amenities'])
            ->latest()
            ->paginate(15);

        return response()->json($dachas);
    }

    /**
     * Yangi dacha e'lonini yaratish
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'capacity' => 'required|integer|min:1',
            'rooms_count' => 'required|integer|min:1',
            'region' => 'required|string|max:255',
            'district' => 'required|string|max:255',
            'mahalla' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'weekday_price' => 'required|numeric|min:0',
            'weekend_price' => 'nullable|numeric|min:0',
            'currency' => 'nullable|in:USD,UZS',
            'amenities' => 'nullable|array',
            'amenities.*' => 'exists:amenities,id',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,webp|max:10240',
            'videos' => 'nullable|array',
            'videos.*' => 'mimes:mp4,mov,avi,mkv|max:51200',
        ]);

        return DB::transaction(function () use ($request, $validated) {
            $dacha = Dacha::create([
                'user_id' => $request->user()->id,
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'capacity' => $validated['capacity'],
                'rooms_count' => $validated['rooms_count'],
                'region' => $validated['region'],
                'district' => $validated['district'],
                'mahalla' => $validated['mahalla'] ?? null,
                'address' => $validated['address'] ?? null,
                'latitude' => $validated['latitude'] ?? null,
                'longitude' => $validated['longitude'] ?? null,
                'weekday_price' => $validated['weekday_price'],
                'weekend_price' => $validated['weekend_price'] ?? null,
                'currency' => $validated['currency'] ?? 'USD',
                'status' => 'active',
            ]);

            // Qulayliklarni biriktirish
            if (!empty($validated['amenities'])) {
                $dacha->amenities()->sync($validated['amenities']);
            }

            // Rasmlarni saqlash
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $path = $image->store('dachas/images', 'public');
                    $dacha->media()->create([
                        'type' => 'image',
                        'path' => $path,
                    ]);
                }
            }

            // Videolarni saqlash
            if ($request->hasFile('videos')) {
                foreach ($request->file('videos') as $video) {
                    $path = $video->store('dachas/videos', 'public');
                    $dacha->media()->create([
                        'type' => 'video',
                        'path' => $path,
                    ]);
                }
            }

            return response()->json([
                'message' => 'Dacha muvaffaqiyatli yaratildi',
                'data' => $dacha->load(['media', 'amenities']),
            ], 201);
        });
    }

    /**
     * Dacha egasining aniq bitta dachasi ma'lumoti
     */
    public function show(Request $request, $id)
    {
        $dacha = $this->findOwnerDacha($request, $id);

        return response()->json($dacha->load(['media', 'amenities']));
    }

    /**
     * Dacha ma'lumotlarini tahrirlash
     */
    public function update(Request $request, $id)
    {
        $dacha = $this->findOwnerDacha($request, $id);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'capacity' => 'sometimes|required|integer|min:1',
            'rooms_count' => 'sometimes|required|integer|min:1',
            'region' => 'sometimes|required|string|max:255',
            'district' => 'sometimes|required|string|max:255',
            'mahalla' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'weekday_price' => 'sometimes|required|numeric|min:0',
            'weekend_price' => 'nullable|numeric|min:0',
            'currency' => 'nullable|in:USD,UZS',
            'status' => 'nullable|in:pending,active,inactive',
            'amenities' => 'nullable|array',
            'amenities.*' => 'exists:amenities,id',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,webp|max:10240',
            'videos' => 'nullable|array',
            'videos.*' => 'mimes:mp4,mov,avi,mkv|max:51200',
        ]);

        return DB::transaction(function () use ($request, $dacha, $validated) {
            $dacha->update($request->only([
                'name', 'description', 'capacity', 'rooms_count',
                'region', 'district', 'mahalla', 'address',
                'latitude', 'longitude', 'weekday_price', 'weekend_price',
                'currency', 'status'
            ]));

            if ($request->has('amenities')) {
                $dacha->amenities()->sync($request->amenities ?? []);
            }

            // Yangi rasmlar qo'shish
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $path = $image->store('dachas/images', 'public');
                    $dacha->media()->create([
                        'type' => 'image',
                        'path' => $path,
                    ]);
                }
            }

            // Yangi videolar qo'shish
            if ($request->hasFile('videos')) {
                foreach ($request->file('videos') as $video) {
                    $path = $video->store('dachas/videos', 'public');
                    $dacha->media()->create([
                        'type' => 'video',
                        'path' => $path,
                    ]);
                }
            }

            return response()->json([
                'message' => 'Dacha muvaffaqiyatli yangilandi',
                'data' => $dacha->fresh(['media', 'amenities']),
            ]);
        });
    }

    /**
     * Dachani o'chirish (fayllari bilan birga)
     */
    public function destroy(Request $request, $id)
    {
        $dacha = $this->findOwnerDacha($request, $id);

        foreach ($dacha->media as $media) {
            if (Storage::disk('public')->exists($media->path)) {
                Storage::disk('public')->delete($media->path);
            }
        }

        $dacha->delete();

        return response()->json([
            'message' => 'Dacha muvaffaqiyatli o\'chirildi',
        ]);
    }

    /**
     * Dacha uchun alohida media yuklash
     */
    public function uploadMedia(Request $request, $id)
    {
        $dacha = $this->findOwnerDacha($request, $id);

        $request->validate([
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,webp|max:10240',
            'videos' => 'nullable|array',
            'videos.*' => 'mimes:mp4,mov,avi,mkv|max:51200',
        ]);

        $uploaded = [];

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('dachas/images', 'public');
                $media = $dacha->media()->create([
                    'type' => 'image',
                    'path' => $path,
                ]);
                $uploaded[] = $media;
            }
        }

        if ($request->hasFile('videos')) {
            foreach ($request->file('videos') as $video) {
                $path = $video->store('dachas/videos', 'public');
                $media = $dacha->media()->create([
                    'type' => 'video',
                    'path' => $path,
                ]);
                $uploaded[] = $media;
            }
        }

        return response()->json([
            'message' => 'Fayllar muvaffaqiyatli yuklandi',
            'data' => $uploaded,
        ]);
    }

    /**
     * Alohida bitta mediani o'chirish
     */
    public function deleteMedia(Request $request, $mediaId)
    {
        $media = DachaMedia::with('dacha')->findOrFail($mediaId);

        // Ownership check
        if ($media->dacha->user_id !== $request->user()->id && !in_array($request->user()->role, ['admin', 'super_admin'])) {
            return response()->json(['message' => 'Ruxsat berilmagan'], 403);
        }

        if (Storage::disk('public')->exists($media->path)) {
            Storage::disk('public')->delete($media->path);
        }

        $media->delete();

        return response()->json([
            'message' => 'Media fayl o\'chirildi',
        ]);
    }

    /**
     * Dacha egasiga tegishli ekanligini tekshirib topish
     */
    protected function findOwnerDacha(Request $request, $id): Dacha
    {
        $query = Dacha::where('id', $id);

        if (!in_array($request->user()->role, ['admin', 'super_admin'])) {
            $query->where('user_id', $request->user()->id);
        }

        return $query->firstOrFail();
    }
}
