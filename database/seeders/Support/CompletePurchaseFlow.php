<?php

namespace Database\Seeders\Support;

use App\Actions\Purchases\ApprovePurchaseOrder;
use App\Actions\Purchases\CancelPurchaseOrder;
use App\Actions\Purchases\GeneratePurchaseOrder;
use App\Actions\Purchases\SyncPurchaseRequestItems;
use App\Actions\Purchases\SyncSupplierQuotationItems;
use App\Actions\Purchases\UpsertQuotationComparison;
use App\Actions\Quotations\EvaluateQuotationScores;
use App\Actions\Requirements\SendRequirementToSuppliers;
use App\Enums\CorrelativeSubject;
use App\Enums\InvitationStatus;
use App\Enums\QuotationCaptureMode;
use App\Enums\QuotationStatus;
use App\Enums\RequirementStatus;
use App\Models\Company;
use App\Models\CostType;
use App\Models\Project;
use App\Models\PurchaseOrder;
use App\Models\QuotationScoreParameter;
use App\Models\Requirement;
use App\Models\RequirementSupplierInvitation;
use App\Models\Supplier;
use App\Models\SupplierQuotation;
use App\Models\User;
use App\Services\Codes\CodeGeneratorService;
use Illuminate\Support\Collection;

class CompletePurchaseFlow
{
    public function __construct(
        protected CodeGeneratorService $codeGenerator,
        protected SyncPurchaseRequestItems $syncRequirementItems,
        protected SyncSupplierQuotationItems $syncQuotationItems,
    ) {}

    /**
     * @param  Collection<int, Supplier>  $suppliers
     * @param  array{
     *     title: string,
     *     requirement_type?: string,
     *     cost_type_code?: string,
     *     priority?: string,
     *     description?: string,
     *     observation?: string,
     *     items: list<array<string, mixed>>,
     *     payment_conditions?: string,
     *     warranty?: string,
     *     quotation_observation?: string,
     *     order_conditions?: string,
     *     order_observation?: string,
     *     item_observations?: list<string>,
     *     approve?: bool,
     *     approval_notes?: string,
     *     cancel?: bool,
     *     cancellation_reason?: string
     * }  $spec
     */
    public function createOrder(
        Company $company,
        Project $project,
        User $responsible,
        Collection $suppliers,
        array $spec,
    ): PurchaseOrder {
        $costType = CostType::query()
            ->where('company_id', $company->id)
            ->where('code', $spec['cost_type_code'] ?? 'MAT')
            ->first();

        $requirement = Requirement::query()->create([
            'company_id' => $company->id,
            'work_project_id' => $project->id,
            'responsible_user_id' => $responsible->id,
            'requested_by' => $responsible->id,
            'code' => $this->codeGenerator->generate($company, $project, CorrelativeSubject::Requirement),
            'title' => $spec['title'],
            'requirement_type' => $spec['requirement_type'] ?? 'material',
            'cost_type_id' => $costType?->id,
            'priority' => $spec['priority'] ?? 'alta',
            'requested_by_name' => $responsible->name,
            'request_date' => now()->subDays(3)->toDateString(),
            'needed_date' => now()->addDays(12)->toDateString(),
            'description' => $spec['description'] ?? $spec['title'],
            'observation' => $spec['observation'] ?? null,
            'status' => RequirementStatus::Created->value(),
        ]);

        $this->syncRequirementItems->handle($requirement, $spec['items']);
        $requirement->update(['status' => RequirementStatus::InProcess->value()]);

        app(SendRequirementToSuppliers::class)->handle(
            $requirement,
            $suppliers->pluck('id')->all(),
            $responsible,
            'Invitación demo para '.$spec['title'],
            now()->addDays(7)->toDateString(),
        );

        $quotations = $suppliers->values()->map(function (Supplier $supplier, int $index) use (
            $company,
            $project,
            $requirement,
            $spec,
        ): SupplierQuotation {
            $factor = $index === 0 ? 1.08 : 1.0;
            $quotedItems = collect($spec['items'])->map(function (array $item) use ($factor): array {
                $unitPrice = round(((float) ($item['estimated_unit_price'] ?? 100)) * $factor, 2);

                return [
                    'product_or_service' => $item['description'],
                    'unit' => $item['unit'],
                    'quantity' => (string) $item['quantity'],
                    'unit_price' => (string) $unitPrice,
                ];
            })->all();

            $subtotal = collect($quotedItems)->sum(
                fn (array $item): float => (float) $item['quantity'] * (float) $item['unit_price'],
            );
            $tax = round($subtotal * 0.18, 2);

            $quotation = SupplierQuotation::query()->create([
                'company_id' => $company->id,
                'work_project_id' => $project->id,
                'requirement_id' => $requirement->id,
                'supplier_id' => $supplier->id,
                'code' => $this->codeGenerator->generate($company, $project, CorrelativeSubject::SupplierQuotation),
                'quotation_number' => 'COT-'.$requirement->id.'-'.($index + 1),
                'quotation_date' => now()->subDay()->toDateString(),
                'valid_until' => now()->addDays(20)->toDateString(),
                'currency' => 'PEN',
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total' => $subtotal + $tax,
                'delivery_time_days' => $index === 0 ? 6 : 10,
                'payment_conditions' => $spec['payment_conditions'] ?? 'Pago a 15 días. Entrega en obra.',
                'warranty' => $spec['warranty'] ?? '12 meses',
                'observation' => $spec['quotation_observation'] ?? 'Cotización demo completa.',
                'capture_mode' => QuotationCaptureMode::Form->value(),
                'status' => QuotationStatus::Registered->value(),
            ]);

            $this->syncQuotationItems->handle($quotation, $quotedItems);

            RequirementSupplierInvitation::query()
                ->where('requirement_id', $requirement->id)
                ->where('supplier_id', $supplier->id)
                ->update(['status' => InvitationStatus::Responded->value()]);

            return $quotation;
        });

        $winner = $quotations->last();
        $parameters = QuotationScoreParameter::query()
            ->where('company_id', $company->id)
            ->get();

        if ($parameters->isNotEmpty()) {
            app(EvaluateQuotationScores::class)->handle(
                $winner,
                $parameters->map(fn (QuotationScoreParameter $parameter, int $index): array => [
                    'parameter_id' => $parameter->id,
                    'score' => $index === 0 ? min(36, (float) $parameter->max_score) : min(16, (float) $parameter->max_score),
                ])->all(),
                $responsible,
            );
        }

        app(UpsertQuotationComparison::class)->handle(
            $requirement,
            $winner,
            $responsible,
            'Selección demo para completar la orden de compra.',
        );

        $order = app(GeneratePurchaseOrder::class)->handle($requirement);

        $order->update([
            'conditions' => $spec['order_conditions'] ?? $winner->payment_conditions,
            'observation' => $spec['order_observation'] ?? $winner->observation,
        ]);

        foreach ($spec['item_observations'] ?? [] as $index => $observation) {
            $item = $order->items->get($index);

            if ($item !== null) {
                $item->update(['observation' => $observation]);
            }
        }

        if ($spec['approve'] ?? false) {
            app(ApprovePurchaseOrder::class)->handle(
                $order,
                $responsible,
                $spec['approval_notes'] ?? 'Aprobada en seeder demo.',
            );
        }

        if ($spec['cancel'] ?? false) {
            app(CancelPurchaseOrder::class)->handle(
                $order,
                $responsible,
                $spec['cancellation_reason'] ?? 'Anulada en seeder demo.',
            );
        }

        return $order->fresh(['items']) ?? $order;
    }
}
