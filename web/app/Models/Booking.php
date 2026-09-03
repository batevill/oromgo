<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'total_price' => 'decimal:2',
    ];

    public function dacha()
    {
        return $this->belongsTo(Dacha::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Mijozning ismi (Dastur orqali yoki tashqi kiritilgan)
     */
    public function getGuestNameAttribute(): string
    {
        if ($this->user) {
            return $this->user->name;
        }
        return $this->customer_name ?: 'Noma\'lum mijoz';
    }

    /**
     * Mijozning telefoni
     */
    public function getGuestPhoneAttribute(): string
    {
        if ($this->user && $this->user->phone) {
            return $this->user->phone;
        }
        return $this->customer_phone ?: '-';
    }

    /**
     * Manba nomi (o'zbekcha)
     */
    public function getSourceLabelAttribute(): string
    {
        return match ($this->source) {
            'telegram' => 'Telegram 📱',
            'phone' => 'Telefon 📞',
            'manual' => 'Qo\'lda / Boshqa 📝',
            default => 'Oromgo Ilova 🌟',
        };
    }
}
