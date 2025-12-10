<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExpenseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'category_id' => $this->category_id,
            'vendor_id' => $this->vendor_id,
            'amount' => (string) $this->amount,
            'date' => $this->date->format('Y-m-d'),
            'description' => $this->description,
            'created_by' => $this->created_by,
            'category' => $this->whenLoaded('category', function () {
                return new ExpenseCategoryResource($this->category);
            }),
            'vendor' => $this->whenLoaded('vendor', function () {
                return $this->vendor ? new VendorResource($this->vendor) : null;
            }),
            'creator' => $this->whenLoaded('creator', function () {
                return new UserResource($this->creator);
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
