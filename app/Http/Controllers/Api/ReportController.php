<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(
        private ReportService $reportService
    ) {
    }

    /**
     * Get summary report of expenses by month/category.
     */
    public function summary(Request $request): JsonResponse
    {
        $summary = $this->reportService->getSummary($request->user());
        return response()->json($summary);
    }
}
