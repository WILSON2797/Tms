<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ModeOfTransport;
use App\Models\ModeOfDelivery;

class ModeController extends Controller
{
    /**
     * Get list of active modes of transport.
     */
    public function modesOfTransport()
    {
        $mots = ModeOfTransport::where('is_active', true)->get();
        return response()->json([
            'success' => true,
            'data' => $mots
        ]);
    }

    /**
     * Get list of active modes of delivery.
     */
    public function modesOfDelivery()
    {
        $mods = ModeOfDelivery::where('is_active', true)->get();
        return response()->json([
            'success' => true,
            'data' => $mods
        ]);
    }
}
