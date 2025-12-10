<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreExpenseRequest;
use App\Http\Requests\UpdateExpenseRequest;
use App\Http\Resources\ExpenseCollection;
use App\Http\Resources\ExpenseResource;
use App\Services\ExpenseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function __construct(
        private ExpenseService $expenseService
    ) {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): ExpenseCollection
    {
        $expenses = $this->expenseService->getAll($request, $request->user());
        return new ExpenseCollection($expenses);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreExpenseRequest $request): JsonResponse
    {
        $expense = $this->expenseService->create($request->validated(), $request->user());
        return (new ExpenseResource($expense))->response()->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id): ExpenseResource|JsonResponse
    {
        $expense = $this->expenseService->findOrFail($id);

        if (!$this->expenseService->canView($expense, $request->user())) {
            return response()->json([
                'message' => 'Unauthorized. You can only view your own expenses.',
            ], 403);
        }

        return new ExpenseResource($expense);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateExpenseRequest $request, string $id): ExpenseResource|JsonResponse
    {
        $expense = $this->expenseService->findOrFail($id);

        if (!$this->expenseService->canView($expense, $request->user())) {
            return response()->json([
                'message' => 'Unauthorized. You can only update your own expenses.',
            ], 403);
        }

        $expense = $this->expenseService->update($expense, $request->validated());
        return new ExpenseResource($expense);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $expense = $this->expenseService->findOrFail($id);

        if (!$this->expenseService->canDelete($expense, $request->user())) {
            return response()->json([
                'message' => 'Unauthorized. You can only delete your own expenses.',
            ], 403);
        }

        $this->expenseService->delete($expense);
        return response()->json(['message' => 'Expense deleted successfully']);
    }
}
