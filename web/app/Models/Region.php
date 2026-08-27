<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    use HasFactory;

    protected $fillable = [
        'soato_id',
        'name',
        'name_uz',
        'name_oz',
        'name_ru',
        'sort_order',
    ];

    public function districts()
    {
        return $this->hasMany(District::class)->orderBy('sort_order')->orderBy('name');
    }

    public function dachas()
    {
        return $this->hasMany(Dacha::class);
    }
}
