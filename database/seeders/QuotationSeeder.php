<?php

namespace Database\Seeders;

use App\Actions\Purchases\SyncSupplierQuotationItems;
use App\Enums\CorrelativeSubject;
use App\Enums\InvitationStatus;
use App\Enums\QuotationCaptureMode;
use App\Enums\QuotationStatus;
use App\Models\Company;
use App\Models\Requirement;
use App\Models\RequirementSupplierInvitation;
use App\Models\Supplier;
use App\Models\SupplierQuotation;
use App\Models\User;
use App\Services\Codes\CodeGeneratorService;
use Illuminate\Database\Seeder;

class QuotationSeeder extends Seeder
{
    public function run(): void
    {
        $codeGenerator = app(CodeGeneratorService::class);
        $syncItems = app(SyncSupplierQuotationItems::class);

        Company::query()->each(function (Company $company) use ($codeGenerator, $syncItems): void {
            $requirement = Requirement::query()
                ->where('company_id', $company->id)
                ->where('title', 'Alquiler de compactadora demo')
                ->first();

            if ($requirement === null) {
                return;
            }

            $project = $requirement->project;
            $responsible = $company->users()->wherePivot('active', true)->first()
                ?? User::query()->first();

            if ($project === null || $responsible === null) {
                return;
            }

            $suppliers = Supplier::query()->where('company_id', $company->id)->orderBy('id')->take(2)->get();

            if ($suppliers->isEmpty()) {
                return;
            }

            $suppliers->values()->each(function (Supplier $supplier, int $index) use (
                $company,
                $project,
                $requirement,
                $responsible,
                $codeGenerator,
                $syncItems,
            ): void {
                if (SupplierQuotation::query()
                    ->where('requirement_id', $requirement->id)
                    ->where('supplier_id', $supplier->id)
                    ->exists()) {
                    return;
                }

                $unitPrice = $index === 0 ? 920 : 850;
                $quantity = 15;
                $subtotal = $unitPrice * $quantity;
                $tax = round($subtotal * 0.18, 2);

                $quotation = SupplierQuotation::query()->create([
                    'company_id' => $company->id,
                    'work_project_id' => $requirement->work_project_id,
                    'requirement_id' => $requirement->id,
                    'supplier_id' => $supplier->id,
                    'code' => $codeGenerator->generate($company, $project, CorrelativeSubject::SupplierQuotation),
                    'quotation_number' => 'COT-ALQ-'.($index + 1),
                    'quotation_date' => now()->toDateString(),
                    'valid_until' => now()->addDays(10)->toDateString(),
                    'currency' => 'PEN',
                    'subtotal' => $subtotal,
                    'tax' => $tax,
                    'total' => $subtotal + $tax,
                    'delivery_time_days' => $index === 0 ? 2 : 3,
                    'payment_conditions' => '50% adelanto y 50% contra parte semanal.',
                    'warranty' => 'Cobertura de fallas mecanicas durante el alquiler',
                    'observation' => 'Incluye operador, combustible y traslado. Horas extra se facturan aparte.',
                    'capture_mode' => QuotationCaptureMode::Form->value(),
                    'status' => QuotationStatus::Registered->value(),
                ]);

                $syncItems->handle($quotation, [
                    [
                        'product_or_service' => 'Alquiler de rodillo compactador 10 t',
                        'unit' => 'día',
                        'quantity' => (string) $quantity,
                        'unit_price' => (string) $unitPrice,
                    ],
                ]);

                RequirementSupplierInvitation::query()->updateOrCreate(
                    [
                        'company_id' => $company->id,
                        'requirement_id' => $requirement->id,
                        'supplier_id' => $supplier->id,
                    ],
                    [
                        'sent_by' => $responsible->id,
                        'sent_at' => now(),
                        'status' => InvitationStatus::Responded->value(),
                        'message' => 'Invitación demo de alquiler de compactadora.',
                        'response_deadline' => now()->addDays(5)->toDateString(),
                    ],
                );
            });
        });
    }
}
