<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTransporterRequest;
use App\Http\Requests\UpdateTransporterRequest;
use App\Http\Resources\TransporterResource;
use App\Services\TransporterService;

class TransporterController extends Controller
{
    protected TransporterService $transporterService;

    public function __construct(TransporterService $transporterService)
    {
        $this->transporterService = $transporterService;
    }

    public function index()
    {
        $transporters = $this->transporterService->getAllTransporters();
        return response()->json([
            'success' => true,
            'message' => 'Daftar transporter berhasil diambil',
            'data' => TransporterResource::collection($transporters)
        ]);
    }

    public function store(StoreTransporterRequest $request)
    {
        $transporter = $this->transporterService->createTransporter($request->validated());
        return response()->json([
            'success' => true,
            'message' => 'Transporter berhasil disimpan',
            'data' => new TransporterResource($transporter)
        ], 201);
    }

    public function show($id)
    {
        $transporter = $this->transporterService->getTransporterById($id);
        return response()->json([
            'success' => true,
            'message' => 'Detail transporter berhasil diambil',
            'data' => new TransporterResource($transporter)
        ]);
    }

    public function update(UpdateTransporterRequest $request, $id)
    {
        $transporter = $this->transporterService->updateTransporter($id, $request->validated());
        return response()->json([
            'success' => true,
            'message' => 'Transporter berhasil diperbarui',
            'data' => new TransporterResource($transporter)
        ]);
    }

    public function destroy($id)
    {
        $this->transporterService->deleteTransporter($id);
        return response()->json([
            'success' => true,
            'message' => 'Transporter berhasil dihapus'
        ]);
    }
}
