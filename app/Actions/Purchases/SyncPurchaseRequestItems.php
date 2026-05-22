<?php

namespace App\Actions\Purchases;

use App\Models\Requirement;

class SyncPurchaseRequestItems
{
    /**
     * @param  list<array<string, mixed>>  $items
     */
    public function handle(Requirement $requirement, array $items): void
    {
        $requirement->items()->delete();

        foreach ($items as $item) {
            $description = trim((string) ($item['description'] ?? $item['product_or_service'] ?? ''));

            if ($description === '') {
                continue;
            }

            $quantity = (float) ($item['quantity'] ?? 0);
            $unitPrice = isset($item['estimated_unit_price']) ? (float) $item['estimated_unit_price'] : null;

            $requirement->items()->create([
                'company_id' => $requirement->company_id,
                'work_project_id' => $requirement->work_project_id,
                'item_type' => $item['item_type'] ?? 'material',
                'description' => $description,
                'unit' => $item['unit'],
                'quantity' => $quantity,
                'technical_specification' => $item['technical_specification'] ?? null,
                'estimated_unit_price' => $unitPrice,
                'estimated_total' => $unitPrice !== null ? round($quantity * $unitPrice, 2) : null,
                'observation' => $item['observation'] ?? null,
            ]);
        }
    }
}
