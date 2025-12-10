<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class ExpenseService
{
    public function getAll(Request $request, User $user): Collection
    {
        $query = Expense::with(['category', 'vendor', 'creator'])
            ->when($request->has('date'), function ($q) use ($request) {
                return $q->whereDate('date', $request->date);
            })
            ->when($request->has('vendor_id'), function ($q) use ($request) {
                return $q->where('vendor_id', $request->vendor_id);
            })
            ->when($request->has('category_id'), function ($q) use ($request) {
                return $q->where('category_id', $request->category_id);
            });

        // Staff can only see their own expenses
        if ($user->isStaff()) {
            $query->where('created_by', $user->id);
        }

        return $query->get();
    }

    public function create(array $data, User $user): Expense
    {
        $expense = Expense::create([
            ...$data,
            'created_by' => $user->id,
        ]);

        return $expense->load(['category', 'vendor', 'creator']);
    }

    public function findOrFail(int $id): Expense
    {
        return Expense::with(['category', 'vendor', 'creator'])->findOrFail($id);
    }

    public function canView(Expense $expense, User $user): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $expense->created_by === $user->id;
    }

    public function canDelete(Expense $expense, User $user): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $expense->created_by === $user->id;
    }

    public function update(Expense $expense, array $data): Expense
    {
        $expense->update($data);
        return $expense->fresh()->load(['category', 'vendor', 'creator']);
    }

    public function delete(Expense $expense): bool
    {
        return $expense->delete();
    }
}

