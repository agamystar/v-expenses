<?php

namespace App\Services;

use App\Models\ExpenseCategory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class ExpenseCategoryService
{
    public function getAll(Request $request): Collection
    {
        return ExpenseCategory::query()
            ->when($request->has('active'), function ($query) use ($request) {
                return $query->where('is_active', $request->boolean('active'));
            })
            ->get();
    }

    public function create(array $data): ExpenseCategory
    {
        return ExpenseCategory::create($data);
    }

    public function update(ExpenseCategory $category, array $data): ExpenseCategory
    {
        $category->update($data);
        return $category->fresh();
    }

    public function delete(ExpenseCategory $category): bool
    {
        if ($category->expenses()->exists()) {
            throw new \Exception('Cannot delete category with linked expenses.');
        }

        return $category->delete();
    }

    public function findOrFail(int $id): ExpenseCategory
    {
        return ExpenseCategory::findOrFail($id);
    }
}

