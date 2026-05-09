<?php

namespace App\Actions\Purchases;

use App\Enums\CorrelativeSubject;
use App\Enums\PurchaseRequestStatus;
use App\Models\Company;
use App\Models\PurchaseRequest;
use App\Models\QuotationComparison;
use App\Models\SupplierQuotation;
use App\Models\User;
use App\Services\Correlatives\IssueCompanyCorrelativeCode;

class UpsertQuotationComparison
{
    public function handle(PurchaseRequest $purchaseRequest, SupplierQuotation $supplierQuotation, User $user, string $selectionReason): QuotationComparison
    {
        $comparison = QuotationComparison::query()->updateOrCreate(
            ['purchase_request_id' => $purchaseRequest->id],
            [
                'company_id' => $purchaseRequest->company_id,
                'work_project_id' => $purchaseRequest->work_project_id,
                'selected_supplier_quotation_id' => $supplierQuotation->id,
                'selected_by' => $user->id,
                'compared_at' => now(),
                'selection_reason' => $selectionReason,
            ],
        );

        $purchaseRequest->update([
            'status' => PurchaseRequestStatus::Awarded->value(),
        ]);

        if ($comparison->comparison_code === null || $comparison->comparison_code === '') {
            $company = Company::query()->findOrFail($comparison->company_id);

            $comparison->forceFill([
                'comparison_code' => app(IssueCompanyCorrelativeCode::class)->issue($company, CorrelativeSubject::QuotationComparison),
            ])->save();
        }

        return $comparison->refresh();
    }
}
