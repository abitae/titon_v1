<?php

namespace Database\Seeders;

use App\Actions\AccountsPayable\RegisterAccountsPayablePayment;
use App\Actions\Banks\RecordBankMovement;
use App\Actions\Orders\RecordOrderConformity;
use App\Actions\Purchases\ApprovePurchaseOrder;
use App\Actions\Purchases\GeneratePurchaseOrder;
use App\Actions\Purchases\SyncPurchaseRequestItems;
use App\Actions\Purchases\SyncSupplierQuotationItems;
use App\Actions\Purchases\UpsertQuotationComparison;
use App\Actions\Quotations\EvaluateQuotationScores;
use App\Actions\Requirements\SendRequirementToSuppliers;
use App\Enums\AccountsPayableStatus;
use App\Enums\BankMovementType;
use App\Enums\CatalogType;
use App\Enums\ConformityResult;
use App\Enums\CorrelativeSubject;
use App\Enums\InvitationStatus;
use App\Enums\ProjectStatus;
use App\Enums\QuotationCaptureMode;
use App\Enums\QuotationStatus;
use App\Enums\RequirementStatus;
use App\Models\AccountsPayable;
use App\Models\BankAccount;
use App\Models\CatalogItem;
use App\Models\Company;
use App\Models\CostType;
use App\Models\PayableDocument;
use App\Models\Project;
use App\Models\QuotationScoreParameter;
use App\Models\Requirement;
use App\Models\RequirementSupplierInvitation;
use App\Models\Supplier;
use App\Models\SupplierQuotation;
use App\Models\User;
use App\Services\Codes\CodeGeneratorService;
use Illuminate\Database\Seeder;

class DemoOperationalSeeder extends Seeder
{
    public function run(): void
    {
        $codeGenerator = app(CodeGeneratorService::class);
        $syncRequirementItems = app(SyncPurchaseRequestItems::class);
        $syncQuotationItems = app(SyncSupplierQuotationItems::class);

        Company::query()->each(function (Company $company) use ($codeGenerator, $syncRequirementItems, $syncQuotationItems): void {
            $company->update(['correlative_prefix' => 'TITON']);

            $responsible = $company->users()->wherePivot('active', true)->first()
                ?? User::query()->first();

            if ($responsible === null) {
                return;
            }

            $materialCostType = CostType::query()
                ->where('company_id', $company->id)
                ->where('code', 'MAT')
                ->first();
            $serviceCostType = CostType::query()
                ->where('company_id', $company->id)
                ->where('code', 'SER')
                ->first();

            $project = Project::query()->create([
                'company_id' => $company->id,
                'code' => sprintf('OBR%03d', $company->id),
                'name' => 'Obra demo '.$company->name,
                'city' => 'Lima',
                'address' => 'Av. Demo 100, Santiago de Surco',
                'client_name' => 'Cliente demo '.$company->name,
                'responsible_user_id' => $responsible->id,
                'start_date' => now()->subMonths(2)->toDateString(),
                'estimated_end_date' => now()->addMonths(6)->toDateString(),
                'estimated_budget' => 500000,
                'status' => ProjectStatus::InProgress->value(),
                'description' => 'Obra de demostración para flujo de compras, almacén y cuentas por pagar.',
            ]);

            $suppliers = collect([
                [
                    'business_name' => 'Proveedor Alpha '.$company->id,
                    'commercial_name' => 'Alpha Electric',
                    'suffix' => 1,
                    'contact_name' => 'Carlos Rivas',
                    'bank_name' => 'BCP',
                ],
                [
                    'business_name' => 'Proveedor Beta '.$company->id,
                    'commercial_name' => 'Beta Materiales',
                    'suffix' => 2,
                    'contact_name' => 'Ana Torres',
                    'bank_name' => 'Interbank',
                ],
            ])->map(fn (array $data): Supplier => Supplier::query()->create([
                'company_id' => $company->id,
                'business_name' => $data['business_name'],
                'commercial_name' => $data['commercial_name'],
                'ruc' => sprintf('20%02d%07d', $company->id, $data['suffix']),
                'contact_name' => $data['contact_name'],
                'phone' => '99988877'.$data['suffix'],
                'email' => 'proveedor'.$company->id.'-'.$data['suffix'].'@demo.test',
                'address' => 'Av. Industrial '.$data['suffix'].'20, Ate',
                'city' => 'Lima',
                'bank_name' => $data['bank_name'],
                'bank_account' => sprintf('191-%08d-0-1%d', $company->id, $data['suffix']),
                'cci' => sprintf('00219100%08d1%d', $company->id, $data['suffix']),
                'status' => 'active',
            ]));

            $parameters = collect([
                ['name' => 'Precio', 'max_score' => 40, 'weight' => 40],
                ['name' => 'Tiempo de entrega', 'max_score' => 20, 'weight' => 20],
                ['name' => 'Calidad técnica', 'max_score' => 20, 'weight' => 20],
                ['name' => 'Condiciones de pago', 'max_score' => 20, 'weight' => 20],
            ])->map(fn (array $row): QuotationScoreParameter => QuotationScoreParameter::query()->create([
                'company_id' => $company->id,
                'description' => 'Parámetro demo',
                'active' => true,
                ...$row,
            ]));

            $requirementDraft = Requirement::query()->create([
                'company_id' => $company->id,
                'work_project_id' => $project->id,
                'responsible_user_id' => $responsible->id,
                'requested_by' => $responsible->id,
                'code' => $codeGenerator->generate($company, $project, CorrelativeSubject::Requirement),
                'title' => 'Requerimiento borrador demo',
                'requirement_type' => 'material',
                'cost_type_id' => $materialCostType?->id,
                'priority' => 'media',
                'requested_by_name' => $responsible->name,
                'request_date' => now()->toDateString(),
                'needed_date' => now()->addDays(15)->toDateString(),
                'description' => 'Requerimiento en borrador para pruebas.',
                'observation' => 'Pendiente de validar cantidades con el residente de obra.',
                'status' => RequirementStatus::Draft->value(),
            ]);

            $syncRequirementItems->handle($requirementDraft, [
                [
                    'item_type' => 'material',
                    'cost_center_ua' => 'UA-CIV-01',
                    'description' => 'Cemento Portland',
                    'unit' => 'bolsa',
                    'quantity' => '100',
                    'technical_specification' => 'Tipo I, 42.5 kg',
                    'estimated_unit_price' => '28.50',
                    'observation' => 'Almacenar en zona seca.',
                ],
            ]);

            $requirement = Requirement::query()->create([
                'company_id' => $company->id,
                'work_project_id' => $project->id,
                'responsible_user_id' => $responsible->id,
                'requested_by' => $responsible->id,
                'code' => $codeGenerator->generate($company, $project, CorrelativeSubject::Requirement),
                'title' => 'Suministro eléctrico obra demo',
                'requirement_type' => 'material',
                'cost_type_id' => $materialCostType?->id,
                'priority' => 'alta',
                'requested_by_name' => $responsible->name,
                'request_date' => now()->subDays(5)->toDateString(),
                'needed_date' => now()->addDays(10)->toDateString(),
                'description' => 'Cableado y tableros para etapa 1.',
                'observation' => 'Urgente para energizar el campamento de obra.',
                'status' => RequirementStatus::Created->value(),
            ]);

            $syncRequirementItems->handle($requirement, [
                [
                    'item_type' => 'material',
                    'cost_center_ua' => 'UA-ELE-01',
                    'description' => 'Cable THW 10 AWG',
                    'unit' => 'rollo',
                    'quantity' => '20',
                    'technical_specification' => 'Norma técnica nacional, 100 m por rollo',
                    'estimated_unit_price' => '580',
                    'observation' => 'Color negro, aislamiento 600 V.',
                ],
                [
                    'item_type' => 'material',
                    'cost_center_ua' => 'UA-ELE-02',
                    'description' => 'Tablero general 24 circuitos',
                    'unit' => 'und',
                    'quantity' => '2',
                    'technical_specification' => 'Gabinete metálico IP54 con barra de tierra',
                    'estimated_unit_price' => '2850',
                    'observation' => 'Incluye breaker principal de 100 A.',
                ],
            ]);

            $requirement->update(['status' => RequirementStatus::InProcess->value()]);

            app(SendRequirementToSuppliers::class)->handle(
                $requirement,
                $suppliers->pluck('id')->all(),
                $responsible,
                'Invitación demo a cotizar.',
                now()->addDays(7)->toDateString(),
            );

            $quotations = $suppliers->map(function (Supplier $supplier, int $index) use (
                $company,
                $project,
                $requirement,
                $codeGenerator,
                $syncQuotationItems,
            ): SupplierQuotation {
                $subtotal = $index === 0 ? 18000 : 16500;
                $tax = round($subtotal * 0.18, 2);

                $quotation = SupplierQuotation::query()->create([
                    'company_id' => $company->id,
                    'work_project_id' => $project->id,
                    'requirement_id' => $requirement->id,
                    'supplier_id' => $supplier->id,
                    'code' => $codeGenerator->generate($company, $project, CorrelativeSubject::SupplierQuotation),
                    'quotation_number' => 'COT-EXT-'.($index + 1),
                    'quotation_date' => now()->subDays(2)->toDateString(),
                    'valid_until' => now()->addDays(14)->toDateString(),
                    'currency' => 'PEN',
                    'subtotal' => $subtotal,
                    'tax' => $tax,
                    'total' => $subtotal + $tax,
                    'delivery_time_days' => $index === 0 ? 5 : 8,
                    'payment_conditions' => 'Pago a 30 días calendario. Entrega en almacén de obra, incluye flete.',
                    'warranty' => '12 meses contra defectos de fabricación',
                    'observation' => $index === 0
                        ? 'Stock inmediato para cable. Tableros en 5 días útiles.'
                        : 'Mejor precio. Entrega en 8 días por programación de planta.',
                    'capture_mode' => QuotationCaptureMode::Form->value(),
                    'status' => QuotationStatus::Registered->value(),
                ]);

                $syncQuotationItems->handle($quotation, [
                    [
                        'product_or_service' => 'Cable THW 10 AWG',
                        'unit' => 'rollo',
                        'quantity' => '20',
                        'unit_price' => (string) ($index === 0 ? 600 : 550),
                    ],
                    [
                        'product_or_service' => 'Tablero general 24 circuitos',
                        'unit' => 'und',
                        'quantity' => '2',
                        'unit_price' => (string) ($index === 0 ? 3000 : 2750),
                    ],
                ]);

                RequirementSupplierInvitation::query()
                    ->where('requirement_id', $requirement->id)
                    ->where('supplier_id', $supplier->id)
                    ->update(['status' => InvitationStatus::Responded->value()]);

                return $quotation;
            });

            $winner = $quotations->last();

            app(EvaluateQuotationScores::class)->handle(
                $winner,
                $parameters->map(fn (QuotationScoreParameter $parameter, int $index): array => [
                    'parameter_id' => $parameter->id,
                    'score' => $index === 0 ? min(38, (float) $parameter->max_score) : min(18, (float) $parameter->max_score),
                ])->all(),
                $responsible,
            );

            app(UpsertQuotationComparison::class)->handle(
                $requirement,
                $winner,
                $responsible,
                'Mejor puntaje ponderado en demo.',
            );

            $order = app(GeneratePurchaseOrder::class)->handle($requirement);

            app(ApprovePurchaseOrder::class)->handle(
                $order,
                $responsible,
                'Aprobación demo de gerencia. Coordinar descarga con almacén de obra.',
            );

            $order->update([
                'conditions' => 'Pago a 30 días calendario. Entrega en almacén de obra, incluye flete e IGV.',
                'observation' => 'Priorizar tableros generales. Verificar certificación de aislamiento del cable.',
            ]);

            $order->items->each(function ($item, int $index): void {
                $item->update([
                    'observation' => $index === 0
                        ? 'Color negro, 100 m por rollo, norma NTP.'
                        : 'Gabinete IP54 con breaker principal de 100 A.',
                ]);
            });

            app(RecordOrderConformity::class)->handle(
                $order,
                $responsible,
                ConformityResult::Conform->value(),
                'Conformidad demo en obra.',
            );

            $accountsPayable = AccountsPayable::query()->where('order_id', $order->id)->first();

            if ($accountsPayable === null) {
                return;
            }

            $accountsPayable->documents()
                ->where('required', true)
                ->get()
                ->each(fn (PayableDocument $document): PayableDocument => $this->attachDemoPayableDocument($document, $responsible));

            $accountsPayable->refresh();

            if ($accountsPayable->requiredDocumentsUploaded()) {
                $accountsPayable->update(['status' => AccountsPayableStatus::ReadyForPayment->value()]);
            }

            $cashAccount = BankAccount::query()->firstOrCreate(
                [
                    'company_id' => $company->id,
                    'is_cash' => true,
                    'name' => 'Caja demo',
                ],
                [
                    'currency' => 'PEN',
                    'account_number' => 'CAJA-'.$company->id,
                    'balance' => 0,
                    'is_active' => true,
                ],
            );

            $paymentAmount = (float) $accountsPayable->balance;

            if ($paymentAmount > 0 && (float) $cashAccount->balance < $paymentAmount) {
                app(RecordBankMovement::class)->handle($cashAccount, $responsible, [
                    'type' => BankMovementType::Deposit->value(),
                    'amount' => $paymentAmount * 2,
                    'movement_date' => now()->toDateString(),
                    'concept' => 'Saldo inicial demo caja',
                    'reference' => 'DemoOperationalSeeder',
                ]);
            }

            $paymentMethod = CatalogItem::query()
                ->where('company_id', $company->id)
                ->where('type', CatalogType::PaymentMethod->value())
                ->where('code', 'EFE')
                ->first();

            if ($paymentMethod !== null && $paymentAmount > 0) {
                app(RegisterAccountsPayablePayment::class)->handle(
                    $accountsPayable->fresh(),
                    [
                        'amount' => $paymentAmount,
                        'payment_date' => now()->toDateString(),
                        'concept' => 'Pago demo '.$accountsPayable->code,
                        'payment_method_id' => $paymentMethod->id,
                        'bank_account_id' => $cashAccount->id,
                        'currency' => $accountsPayable->currency,
                    ],
                    $responsible,
                );
            }

            $serviceRequirement = Requirement::query()->create([
                'company_id' => $company->id,
                'work_project_id' => $project->id,
                'responsible_user_id' => $responsible->id,
                'requested_by' => $responsible->id,
                'code' => $codeGenerator->generate($company, $project, CorrelativeSubject::Requirement),
                'title' => 'Servicio de instalación',
                'requirement_type' => 'servicio',
                'cost_type_id' => $serviceCostType?->id,
                'priority' => 'media',
                'requested_by_name' => $responsible->name,
                'request_date' => now()->toDateString(),
                'needed_date' => now()->addDays(20)->toDateString(),
                'status' => RequirementStatus::InProcess->value(),
                'description' => 'Orden de servicio pendiente de cierre.',
                'observation' => 'Coordinar ventana de corte eléctrico con el residente.',
            ]);

            $syncRequirementItems->handle($serviceRequirement, [
                [
                    'item_type' => 'servicio',
                    'cost_center_ua' => 'UA-SER-01',
                    'description' => 'Instalación y conexionado de tableros',
                    'unit' => 'glb',
                    'quantity' => '1',
                    'technical_specification' => 'Incluye pruebas de aislamiento y protocolo de energización',
                    'estimated_unit_price' => '4500',
                    'observation' => 'Mano de obra calificada y EPP obligatorio.',
                ],
            ]);
        });
    }

    protected function attachDemoPayableDocument(PayableDocument $document, User $responsible): PayableDocument
    {
        $demoDirectory = storage_path('app/demo-seed');

        if (! is_dir($demoDirectory)) {
            mkdir($demoDirectory, 0755, true);
        }

        $fileName = $document->document_type.'.pdf';
        $filePath = $demoDirectory.DIRECTORY_SEPARATOR.$fileName;

        if (! is_file($filePath)) {
            file_put_contents($filePath, '%PDF-1.4 demo document');
        }

        $document->clearMediaCollection('archivo');

        $document
            ->addMedia($filePath)
            ->usingFileName($fileName)
            ->usingName($document->typeLabel())
            ->toMediaCollection('archivo', 'public');

        $document->update([
            'uploaded' => true,
            'uploaded_by' => $responsible->id,
            'uploaded_at' => now(),
            'status' => 'cargado',
        ]);

        return $document->refresh();
    }
}
