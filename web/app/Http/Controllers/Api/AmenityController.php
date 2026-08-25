<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Amenity;
use Illuminate\Http\Request;

class AmenityController extends Controller
{
    /**
     * Barcha qulayliklar ro'yxati (Basseyn, Sauna, WiFi va h.k.)
     */
    public function index()
    {
        return response()->json(Amenity::all());
    }
}
