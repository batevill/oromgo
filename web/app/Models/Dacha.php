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
}
