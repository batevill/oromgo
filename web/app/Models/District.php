<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    use HasFactory;

    protected $fillable = [
        'region_id',
        'soato_id',
        'name',
        'name_uz',
        'name_oz',
        'name_ru',
        'sort_order',
    ];

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function dachas()
    {
        return $this->hasMany(Dacha::class);
    }
}
