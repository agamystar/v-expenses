<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVendorRequest;
use App\Http\Requests\UpdateVendorRequest;
use App\Http\Resources\VendorCollection;
use App\Http\Resources\VendorResource;
use App\Services\VendorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    public function __construct(
        private VendorService $vendorService
    ) {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): VendorCollection
    {
        $vendors = $this->vendorService->getAll($request);
        return new VendorCollection($vendors);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreVendorRequest $request): JsonResponse
    {
        $vendor = $this->vendorService->create($request->validated());
        return (new VendorResource($vendor))->response()->setStatusCode(201);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateVendorRequest $request, string $id): VendorResource
    {
        $vendor = $this->vendorService->findOrFail($id);
        $vendor = $this->vendorService->update($vendor, $request->validated());
        return new VendorResource($vendor);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $vendor = $this->vendorService->findOrFail($id);
        $this->vendorService->delete($vendor);
        return response()->json(['message' => 'Vendor deleted successfully']);
    }
}
