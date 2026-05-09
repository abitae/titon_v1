<?php

namespace App\Actions\Payments;

use App\Enums\CorrelativeSubject;
use App\Models\Company;
use App\Models\SupplierPayment;
use App\Services\Correlatives\IssueCompanyCorrelativeCode;

class RegisterSupplierPayment
{
    public function __construct(
        protected RefreshPaymentScheduleStatus $refreshPaymentScheduleStatus,
    ) {}

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function handle(array $attributes): SupplierPayment
    {
        $company = Company::query()->findOrFail((int) $attributes['company_id']);

        if (! array_key_exists('registry_code', $attributes) || $attributes['registry_code'] === null || $attributes['registry_code'] === '') {
            $attributes['registry_code'] = app(IssueCompanyCorrelativeCode::class)->issue($company, CorrelativeSubject::SupplierPayment);
        }

        $payment = SupplierPayment::query()->create($attributes);

        if ($payment->schedule !== null) {
            $this->refreshPaymentScheduleStatus->handle($payment->schedule);
        }

        return $payment->refresh();
    }
}
