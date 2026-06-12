<?php

namespace App\Actions\Purchases;

use App\Enums\CorrelativeSubject;
use App\Enums\QuotationStatus;
use App\Enums\RequirementStatus;
use App\Models\Company;
use App\Models\QuotationComparison;
use App\Models\Requirement;
use App\Models\SupplierQuotation;
use App\Models\User;
use App\Services\Codes\CodeGeneratorService;
use Illuminate\Support\Facades\DB;

class UpsertQuotationComparison
{
    public function handle(Requirement $requirement, SupplierQuotation $supplierQuotation, User $user, string $selectionReason): QuotationComparison
    {
        return DB::transaction(fn (): QuotationComparison => $this->persistComparison($requirement, $supplierQuotation, $user, $selectionReason));
    }

    protected function persistComparison(Requirement $requirement, SupplierQuotation $supplierQuotation, User $user, string $selectionReason): QuotationComparison
    {
        $previousQuotationId = QuotationComparison::query()
            ->where('requirement_id', $requirement->id)
            ->value('selected_supplier_quotation_id');

        $attributes = [
            'company_id' => $requirement->company_id,
            'work_project_id' => $requirement->work_project_id,
            'selected_supplier_quotation_id' => $supplierQuotation->id,
            'selected_by' => $user->id,
            'compared_at' => now(),
            'selection_reason' => $selectionReason,
        ];

        if ($previousQuotationId !== null && (int) $previousQuotationId !== $supplierQuotation->id) {
            $attributes['order_code'] = null;
            $attributes['order_generated_at'] = null;
        }

        $comparison = QuotationComparison::query()->updateOrCreate(
            ['requirement_id' => $requirement->id],
            $attributes,
        );

        $requirement->update([
            'status' => RequirementStatus::InProcess->value(),
        ]);

        $supplierQuotation->update(['status' => QuotationStatus::Selected->value()]);

        SupplierQuotation::query()
            ->where('requirement_id', $requirement->id)
            ->where('id', '!=', $supplierQuotation->id)
            ->update(['status' => QuotationStatus::NotSelected->value()]);

        if ($comparison->comparison_code === null || $comparison->comparison_code === '') {
            $company = Company::query()->findOrFail($comparison->company_id);
            $project = $requirement->project()->firstOrFail();

            $comparison->forceFill([
                'comparison_code' => app(CodeGeneratorService::class)->generate(
                    $company,
                    $project,
                    CorrelativeSubject::QuotationComparison,
                ),
            ])->save();
        }

        return $comparison->refresh();
    }
}
