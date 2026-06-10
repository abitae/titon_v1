<?php

namespace App\Livewire\Purchases;

use App\Models\PurchaseRequest;
use Livewire\Component;

class SelectWinningQuotation extends Component
{
    public function mount(PurchaseRequest $purchaseRequest): void
    {
        $this->redirectRoute('purchases.comparison', [
            'purchaseRequest' => $purchaseRequest,
            'selectWinner' => 1,
        ], navigate: true);
    }
}
