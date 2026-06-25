<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ModeOfTransport;
use App\Models\ModeOfDelivery;
use Illuminate\Http\Request;

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

    // --- Mode of Transport CRUD (Management) ---

    public function indexTransport()
    {
        $mots = ModeOfTransport::orderBy('code')->get();
        return response()->json([
            'success' => true,
            'message' => 'Daftar Mode of Transport berhasil diambil',
            'data' => $mots
        ]);
    }

    public function storeTransport(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:modes_of_transport,code',
            'name' => 'required|string|max:255',
            'is_active' => 'required|boolean'
        ]);

        $mot = ModeOfTransport::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Mode of Transport berhasil disimpan',
            'data' => $mot
        ], 201);
    }

    public function showTransport($id)
    {
        $mot = ModeOfTransport::findOrFail($id);
        return response()->json([
            'success' => true,
            'message' => 'Detail Mode of Transport berhasil diambil',
            'data' => $mot
        ]);
    }

    public function updateTransport(Request $request, $id)
    {
        $mot = ModeOfTransport::findOrFail($id);

        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:modes_of_transport,code,' . $mot->id,
            'name' => 'required|string|max:255',
            'is_active' => 'required|boolean'
        ]);

        $mot->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Mode of Transport berhasil diperbarui',
            'data' => $mot
        ]);
    }

    public function destroyTransport($id)
    {
        $mot = ModeOfTransport::findOrFail($id);
        $mot->delete();

        return response()->json([
            'success' => true,
            'message' => 'Mode of Transport berhasil dihapus'
        ]);
    }

    // --- Mode of Delivery CRUD (Management) ---

    public function indexDelivery()
    {
        $mods = ModeOfDelivery::orderBy('code')->get();
        return response()->json([
            'success' => true,
            'message' => 'Daftar Mode of Delivery berhasil diambil',
            'data' => $mods
        ]);
    }

    public function storeDelivery(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:modes_of_delivery,code',
            'name' => 'required|string|max:255',
            'is_active' => 'required|boolean'
        ]);

        $mod = ModeOfDelivery::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Mode of Delivery berhasil disimpan',
            'data' => $mod
        ], 201);
    }

    public function showDelivery($id)
    {
        $mod = ModeOfDelivery::findOrFail($id);
        return response()->json([
            'success' => true,
            'message' => 'Detail Mode of Delivery berhasil diambil',
            'data' => $mod
        ]);
    }

    public function updateDelivery(Request $request, $id)
    {
        $mod = ModeOfDelivery::findOrFail($id);

        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:modes_of_delivery,code,' . $mod->id,
            'name' => 'required|string|max:255',
            'is_active' => 'required|boolean'
        ]);

        $mod->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Mode of Delivery berhasil diperbarui',
            'data' => $mod
        ]);
    }

    public function destroyDelivery($id)
    {
        $mod = ModeOfDelivery::findOrFail($id);
        $mod->delete();

        return response()->json([
            'success' => true,
            'message' => 'Mode of Delivery berhasil dihapus'
        ]);
    }
}
