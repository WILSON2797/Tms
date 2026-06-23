<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDriverRequest;
use App\Http\Requests\UpdateDriverRequest;
use App\Http\Resources\DriverResource;
use App\Services\DriverService;

class DriverController extends Controller
{
    protected DriverService $driverService;

    public function __construct(DriverService $driverService)
    {
        $this->driverService = $driverService;
    }

    public function index()
    {
        $drivers = $this->driverService->getAllDrivers();
        return response()->json([
            'success' => true,
            'message' => 'Daftar driver berhasil diambil',
            'data' => DriverResource::collection($drivers)
        ]);
    }

    public function store(StoreDriverRequest $request)
    {
        $data = $request->validated();
        if ($request->hasFile('photo_file')) {
            $data['photo_file'] = $request->file('photo_file');
        }
        
        $driver = $this->driverService->createDriver($data);
        return response()->json([
            'success' => true,
            'message' => 'Driver berhasil disimpan',
            'data' => new DriverResource($driver)
        ], 201);
    }

    public function show($id)
    {
        $driver = $this->driverService->getDriverById($id);
        return response()->json([
            'success' => true,
            'message' => 'Detail driver berhasil diambil',
            'data' => new DriverResource($driver)
        ]);
    }

    public function update(UpdateDriverRequest $request, $id)
    {
        $data = $request->validated();
        if ($request->hasFile('photo_file')) {
            $data['photo_file'] = $request->file('photo_file');
        }
        
        $driver = $this->driverService->updateDriver($id, $data);
        return response()->json([
            'success' => true,
            'message' => 'Driver berhasil diperbarui',
            'data' => new DriverResource($driver)
        ]);
    }

    public function destroy($id)
    {
        $this->driverService->deleteDriver($id);
        return response()->json([
            'success' => true,
            'message' => 'Driver berhasil dihapus'
        ]);
    }
}
