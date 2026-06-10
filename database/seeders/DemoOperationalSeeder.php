<?php

namespace Database\Seeders;

use App\Actions\AccountsPayable\RegisterAccountsPayablePayment;
use App\Actions\Banks\RecordBankMovement;
use App\Actions\Orders\RecordOrderConformity;
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
use App\Enums\OrderStatus;
use App\Enums\ProjectStatus;
use App\Enums\QuotationStatus;
use App\Enums\RequirementStatus;
use App\Models\AccountsPayable;
use App\Models\BankAccount;
use App\Models\CatalogItem;
use App\Models\Company;
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

            $project = Project::query()->create([
                'company_id' => $company->id,
                'code' => sprintf('OBR%03d', $company->id),
                'name' => 'Obra demo '.$company->name,
                'city' => 'Lima',
                'address' => 'Av. Demo 100',
                'client_name' => 'Cliente demo',
                'responsible_user_id' => $responsible->id,
                'start_date' => now()->subMonths(2)->toDateString(),
                'estimated_end_date' => now()->addMonths(6)->toDateString(),
                'estimated_budget' => 500000,
                'status' => ProjectStatus::InProgress->value(),
                'description' => 'Obra de demostración para flujo de compras.',
            ]);

            $suppliers = collect([
                ['business_name' => 'Proveedor Alpha '.$company->id, 'suffix' => 1],
                ['business_name' => 'Proveedor Beta '.$company->id, 'suffix' => 2],
            ])->map(fn (array $data): Supplier => Supplier::query()->create([
                'company_id' => $company->id,
                'business_name' => $data['business_name'],
                'ruc' => sprintf('20%02d%07d', $company->id, $data['suffix']),
                'contact_name' => 'Contacto demo',
                'phone' => '999888777',
                'email' => 'proveedor'.$company->id.'-'.$data['suffix'].'@demo.test',
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
                'priority' => 'media',
                'request_date' => now()->toDateString(),
                'needed_date' => now()->addDays(15)->toDateString(),
                'description' => 'Requerimiento en borrador para pruebas.',
                'status' => RequirementStatus::Draft->value(),
            ]);

            $syncRequirementItems->handle($requirementDraft, [
                [
                    'item_type' => 'material',
                    'description' => 'Cemento Portland',
                    'unit' => 'bolsa',
                    'quantity' => '100',
                    'technical_specification' => 'Tipo I',
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
                'priority' => 'alta',
                'request_date' => now()->subDays(5)->toDateString(),
                'needed_date' => now()->addDays(10)->toDateString(),
                'description' => 'Cableado y tableros para etapa 1.',
                'status' => RequirementStatus::Created->value(),
            ]);

            $syncRequirementItems->handle($requirement, [
                [
                    'item_type' => 'material',
                    'description' => 'Cable THW 10 AWG',
                    'unit' => 'rollo',
                    'quantity' => '20',
                    'technical_specification' => 'Norma técnica nacional',
                ],
                [
                    'item_type' => 'material',
                    'description' => 'Tablero general 24 circuitos',
                    'unit' => 'und',
                    'quantity' => '2',
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
                    'payment_conditions' => '30 días',
                    'warranty' => '12 meses',
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
            $order->update(['status' => OrderStatus::Attended->value()]);

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

            Requirement::query()->create([
                'company_id' => $company->id,
                'work_project_id' => $project->id,
                'responsible_user_id' => $responsible->id,
                'requested_by' => $responsible->id,
                'code' => $codeGenerator->generate($company, $project, CorrelativeSubject::Requirement),
                'title' => 'Servicio de instalación',
                'requirement_type' => 'servicio',
                'priority' => 'media',
                'request_date' => now()->toDateString(),
                'status' => RequirementStatus::InProcess->value(),
                'description' => 'Orden de servicio pendiente de cierre.',
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
