<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CityController extends Controller
{
    /**
     * Get list of active cities for dropdown options.
     */
    public function index()
    {
        $cities = City::where('is_active', true)
            ->orderBy('province')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $cities
        ]);
    }

    /**
     * Get list of all cities for management screen.
     */
    public function indexAll()
    {
        $cities = City::orderBy('province')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar Kabupaten/Kota berhasil diambil',
            'data' => $cities
        ]);
    }

    /**
     * Store a newly created city in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                // Unique check on combination of name and province
                Rule::unique('cities')->where(function ($query) use ($request) {
                    return $query->where('province', $request->province);
                })
            ],
            'type' => 'required|string|in:Kabupaten,Kota',
            'province' => 'required|string|max:255',
            'is_active' => 'required|boolean'
        ], [
            'name.unique' => 'Kabupaten/Kota dengan nama tersebut sudah ada di provinsi yang sama.'
        ]);

        $city = City::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Kabupaten/Kota berhasil disimpan',
            'data' => $city
        ], 201);
    }

    /**
     * Display the specified city.
     */
    public function show($id)
    {
        $city = City::findOrFail($id);
        return response()->json([
            'success' => true,
            'message' => 'Detail Kabupaten/Kota berhasil diambil',
            'data' => $city
        ]);
    }

    /**
     * Update the specified city in storage.
     */
    public function update(Request $request, $id)
    {
        $city = City::findOrFail($id);

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                // Unique check on combination of name and province, excluding current ID
                Rule::unique('cities')->where(function ($query) use ($request) {
                    return $query->where('province', $request->province);
                })->ignore($city->id)
            ],
            'type' => 'required|string|in:Kabupaten,Kota',
            'province' => 'required|string|max:255',
            'is_active' => 'required|boolean'
        ], [
            'name.unique' => 'Kabupaten/Kota dengan nama tersebut sudah ada di provinsi yang sama.'
        ]);

        $city->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Kabupaten/Kota berhasil diperbarui',
            'data' => $city
        ]);
    }

    /**
     * Remove the specified city from storage.
     */
    public function destroy($id)
    {
        $city = City::findOrFail($id);
        $city->delete();

        return response()->json([
            'success' => true,
            'message' => 'Kabupaten/Kota berhasil dihapus'
        ]);
    }
}
