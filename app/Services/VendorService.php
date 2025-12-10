<?php

namespace App\Services;

use App\Models\Vendor;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class VendorService
{
    public function getAll(Request $request): Collection
    {
        return Vendor::query()
            ->when($request->has('active'), function ($query) use ($request) {
                return $query->where('is_active', $request->boolean('active'));
            })
            ->get();
    }

    public function create(array $data): Vendor
    {
        return Vendor::create($data);
    }

    public function update(Vendor $vendor, array $data): Vendor
    {
        $vendor->update($data);
        return $vendor->fresh();
    }

    public function delete(Vendor $vendor): bool
    {
        return $vendor->delete();
    }

    public function findOrFail(int $id): Vendor
    {
        return Vendor::findOrFail($id);
    }
}

