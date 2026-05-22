<?php

namespace App\Actions\AccountsPayable;

use App\Enums\PayableDocumentType;
use App\Models\AccountsPayable;
use App\Models\PayableDocument;

class InitializePayableDocuments
{
    public function handle(AccountsPayable $accountsPayable): void
    {
        foreach (PayableDocumentType::cases() as $type) {
            PayableDocument::query()->firstOrCreate(
                [
                    'accounts_payable_id' => $accountsPayable->id,
                    'document_type' => $type->value(),
                ],
                [
                    'company_id' => $accountsPayable->company_id,
                    'required' => $type->isRequiredByDefault(),
                    'uploaded' => false,
                    'status' => 'pendiente',
                ],
            );
        }
    }
}
