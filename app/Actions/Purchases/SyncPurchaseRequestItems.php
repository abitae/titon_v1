<?php

namespace App\Actions\Purchases;

use App\Models\PurchaseRequest;

class SyncPurchaseRequestItems
{
    /**
     * @param  list<array<string, mixed>>  $items
     */
    public function handle(PurchaseRequest $purchaseRequest, array $items): void
    {
        $purchaseRequest->items()->delete();

        foreach ($items as $item) {
            if (($item['product_or_service'] ?? '') === '') {
                continue;
            }

            $purchaseRequest->items()->create([
                'company_id' => $purchaseRequest->company_id,
                'work_project_id' => $purchaseRequest->work_project_id,
                'product_or_service' => $item['product_or_service'],
                'unit' => $item['unit'],
                'quantity' => $item['quantity'],
                'technical_specification' => $item['technical_specification'] ?: null,
                'observation' => $item['observation'] ?: null,
            ]);
        }
    }
}
