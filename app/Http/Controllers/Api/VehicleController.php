<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVehicleRequest;
use App\Http\Requests\UpdateVehicleRequest;
use App\Http\Resources\VehicleResource;
use App\Services\VehicleService;

class VehicleController extends Controller
{
    protected VehicleService $vehicleService;

    public function __construct(VehicleService $vehicleService)
    {
        $this->vehicleService = $vehicleService;
    }

    public function index()
    {
        $vehicles = $this->vehicleService->getAllVehicles();
        return response()->json([
            'success' => true,
            'message' => 'Daftar kendaraan berhasil diambil',
            'data' => VehicleResource::collection($vehicles)
        ]);
    }

    public function store(StoreVehicleRequest $request)
    {
        $vehicle = $this->vehicleService->createVehicle($request->validated());
        return response()->json([
            'success' => true,
            'message' => 'Kendaraan berhasil disimpan',
            'data' => new VehicleResource($vehicle)
        ], 201);
    }

    public function show($id)
    {
        $vehicle = $this->vehicleService->getVehicleById($id);
        return response()->json([
            'success' => true,
            'message' => 'Detail kendaraan berhasil diambil',
            'data' => new VehicleResource($vehicle)
        ]);
    }

    public function update(UpdateVehicleRequest $request, $id)
    {
        $vehicle = $this->vehicleService->updateVehicle($id, $request->validated());
        return response()->json([
            'success' => true,
            'message' => 'Kendaraan berhasil diperbarui',
            'data' => new VehicleResource($vehicle)
        ]);
    }

    public function destroy($id)
    {
        $this->vehicleService->deleteVehicle($id);
        return response()->json([
            'success' => true,
            'message' => 'Kendaraan berhasil dihapus'
        ]);
    }
}
