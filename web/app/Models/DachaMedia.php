<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class DachaMedia extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $appends = ['url'];

    public function dacha()
    {
        return $this->belongsTo(Dacha::class);
    }

    public function getUrlAttribute(): string
    {
        if (filter_var($this->path, FILTER_VALIDATE_URL)) {
            return $this->path;
        }

        return asset('storage/' . ltrim($this->path, '/'));
    }
}
