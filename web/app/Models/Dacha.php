<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dacha extends Model
{
    /** @use HasFactory<\Database\Factories\DachaFactory> */
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'weekday_price' => 'decimal:2',
        'weekend_price' => 'decimal:2',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    protected $appends = ['avg_rating', 'reviews_count'];

    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function media()
    {
        return $this->hasMany(DachaMedia::class);
    }

    public function amenities()
    {
        return $this->belongsToMany(Amenity::class, 'dacha_amenity');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function getAvgRatingAttribute(): float
    {
        $avg = $this->reviews()->avg('rating');
        return $avg ? (float) round($avg, 1) : 5.0;
    }

    public function getReviewsCountAttribute(): int
    {
        return $this->reviews()->count();
    }
}
