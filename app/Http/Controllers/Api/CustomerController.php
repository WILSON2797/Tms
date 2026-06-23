<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Http\Resources\CustomerResource;
use App\Services\CustomerService;

class CustomerController extends Controller
{
    protected CustomerService $customerService;

    public function __construct(CustomerService $customerService)
    {
        $this->customerService = $customerService;
    }

    public function index()
    {
        $customers = $this->customerService->getAllCustomers();
        return response()->json([
            'success' => true,
            'message' => 'Daftar customer berhasil diambil',
            'data' => CustomerResource::collection($customers)
        ]);
    }

    public function store(StoreCustomerRequest $request)
    {
        $customer = $this->customerService->createCustomer($request->validated());
        return response()->json([
            'success' => true,
            'message' => 'Customer berhasil disimpan',
            'data' => new CustomerResource($customer)
        ], 201);
    }

    public function show($id)
    {
        $customer = $this->customerService->getCustomerById($id);
        return response()->json([
            'success' => true,
            'message' => 'Detail customer berhasil diambil',
            'data' => new CustomerResource($customer)
        ]);
    }

    public function update(UpdateCustomerRequest $request, $id)
    {
        $customer = $this->customerService->updateCustomer($id, $request->validated());
        return response()->json([
            'success' => true,
            'message' => 'Customer berhasil diperbarui',
            'data' => new CustomerResource($customer)
        ]);
    }

    public function destroy($id)
    {
        $this->customerService->deleteCustomer($id);
        return response()->json([
            'success' => true,
            'message' => 'Customer berhasil dihapus'
        ]);
    }
}
