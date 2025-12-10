<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class ReportService
{
    public function getSummary(User $user): array
    {
        $query = \App\Models\Expense::query();

        // Staff can only see their own expenses
        if ($user->isStaff()) {
            $query->where('created_by', $user->id);
        }

        $summary = $query
            ->select(
                DB::raw('DATE_FORMAT(date, "%Y-%m") as month'),
                'category_id',
                DB::raw('SUM(amount) as total_amount'),
                DB::raw('COUNT(*) as expense_count')
            )
            ->with('category:id,name')
            ->groupBy('month', 'category_id')
            ->orderBy('month', 'desc')
            ->orderBy('category_id')
            ->get()
            ->map(function ($item) {
                return [
                    'month' => $item->month,
                    'category_id' => $item->category_id,
                    'category_name' => $item->category->name ?? 'Unknown',
                    'total_amount' => (float) $item->total_amount,
                    'expense_count' => $item->expense_count,
                ];
            });

        return [
            'summary' => $summary,
            'total_expenses' => $summary->sum('total_amount'),
            'total_count' => $summary->sum('expense_count'),
        ];
    }
}

