<?php

namespace App\Actions\Purchases;

use App\Models\SupplierQuotation;
use Illuminate\Support\Facades\DB;

class SyncSupplierQuotationItems
{
    /**
     * @param  list<array<string, mixed>>  $items
     */
    public function handle(SupplierQuotation $supplierQuotation, array $items): void
    {
        DB::transaction(function () use ($supplierQuotation, $items): void {
            $this->syncItems($supplierQuotation, $items);
        });
    }

    /**
     * @param  list<array<string, mixed>>  $items
     */
    protected function syncItems(SupplierQuotation $supplierQuotation, array $items): void
    {
        $supplierQuotation->items()->delete();

        foreach ($items as $item) {
            if (($item['product_or_service'] ?? '') === '') {
                continue;
            }

            $quantity = (float) $item['quantity'];
            $unitPrice = (float) $item['unit_price'];

            $supplierQuotation->items()->create([
                'company_id' => $supplierQuotation->company_id,
                'work_project_id' => $supplierQuotation->work_project_id,
                'product_or_service' => $item['product_or_service'],
                'unit' => $item['unit'],
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'total' => $quantity * $unitPrice,
            ]);
        }
    }
}
