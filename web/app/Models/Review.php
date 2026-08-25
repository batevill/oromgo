<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'rating' => 'integer',
        'cleanliness_rating' => 'integer',
        'comfort_rating' => 'integer',
    ];

    public function dacha()
    {
        return $this->belongsTo(Dacha::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
