<?php

namespace App\Livewire\Payments;

use App\Actions\Payments\RefreshPaymentScheduleStatus;
use App\Concerns\InteractsWithToast;
use App\Enums\ContractPaymentScheduleStatus;
use App\Enums\CorrelativeSubject;
use App\Models\Company;
use App\Models\ContractPaymentSchedule;
use App\Models\SupplierContract;
use App\Services\Correlatives\IssueCompanyCorrelativeCode;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Component;

class ManagePaymentSchedules extends Component
{
    use InteractsWithToast;

    public string $title = 'Cronograma de pagos';

    public SupplierContract $supplierContract;

    public bool $showFormModal = false;

    public ?int $editingScheduleId = null;

    public string $installment_number = '1';

    public string $description = '';

    public string $due_date = '';

    public string $scheduled_amount = '0';

    public string $status = 'pendiente';

    public function mount(SupplierContract $supplierContract): void
    {
        $this->supplierContract = $supplierContract->load(['project', 'supplier']);
        $this->due_date = now()->toDateString();
    }

    public function render(RefreshPaymentScheduleStatus $refreshPaymentScheduleStatus): View
    {
        $schedules = $this->supplierContract->paymentSchedules()->with('payments')->get()->map(function (ContractPaymentSchedule $schedule) use ($refreshPaymentScheduleStatus): ContractPaymentSchedule {
            return $refreshPaymentScheduleStatus->handle($schedule);
        });

        return view('livewire.payments.manage-payment-schedules', [
            'schedules' => $schedules,
            'statusOptions' => ContractPaymentScheduleStatus::cases(),
        ])->layout('layouts.app', ['title' => $this->title]);
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->showFormModal = true;
    }

    public function openEditModal(int $scheduleId): void
    {
        $schedule = $this->supplierContract->paymentSchedules()->findOrFail($scheduleId);

        $this->editingScheduleId = $schedule->id;
        $this->installment_number = (string) $schedule->installment_number;
        $this->description = $schedule->description;
        $this->due_date = $schedule->due_date?->format('Y-m-d') ?? '';
        $this->scheduled_amount = (string) $schedule->scheduled_amount;
        $this->status = $schedule->status;
        $this->showFormModal = true;
    }

    public function saveSchedule(): void
    {
        abort_unless(auth()->user()->can($this->editingScheduleId ? 'payments.editar' : 'payments.crear'), 403);

        $validated = $this->validate([
            'installment_number' => [
                'required',
                'integer',
                'min:1',
                Rule::unique('contract_payment_schedules', 'installment_number')
                    ->where(fn ($query) => $query->where('supplier_contract_id', $this->supplierContract->id))
                    ->ignore($this->editingScheduleId),
            ],
            'description' => ['required', 'string', 'max:255'],
            'due_date' => ['required', 'date'],
            'scheduled_amount' => ['required', 'numeric', 'min:0.01'],
            'status' => ['required', Rule::in(ContractPaymentScheduleStatus::values())],
        ]);
        $isEditing = $this->editingScheduleId !== null;

        $paidAmount = $this->editingScheduleId
            ? (float) $this->supplierContract->paymentSchedules()->findOrFail($this->editingScheduleId)->paid_amount
            : 0.0;

        $scheduledAmount = (float) $validated['scheduled_amount'];

        $company = Company::query()->findOrFail($this->supplierContract->company_id);
        $issuer = app(IssueCompanyCorrelativeCode::class);

        if ($this->editingScheduleId) {
            $existingSchedule = $this->supplierContract->paymentSchedules()->findOrFail($this->editingScheduleId);
            $registryCode = $existingSchedule->registry_code;
            if ($registryCode === null || $registryCode === '') {
                $registryCode = $issuer->issue($company, CorrelativeSubject::ContractPaymentSchedule);
            }
        } else {
            $registryCode = $issuer->issue($company, CorrelativeSubject::ContractPaymentSchedule);
        }

        $this->supplierContract->paymentSchedules()->updateOrCreate(
            ['id' => $this->editingScheduleId],
            [
                'company_id' => $this->supplierContract->company_id,
                'registry_code' => $registryCode,
                'installment_number' => (int) $validated['installment_number'],
                'description' => $validated['description'],
                'due_date' => $validated['due_date'],
                'scheduled_amount' => $scheduledAmount,
                'paid_amount' => $paidAmount,
                'balance' => max(0, $scheduledAmount - $paidAmount),
                'status' => $validated['status'],
            ],
        );

        $this->resetForm();
        $this->successToast($isEditing ? 'Cuota actualizada correctamente.' : 'Cuota creada correctamente.');
    }

    public function closeModal(): void
    {
        $this->showFormModal = false;
    }

    protected function resetForm(): void
    {
        $this->reset([
            'editingScheduleId',
            'description',
        ]);

        $this->installment_number = (string) ($this->supplierContract->paymentSchedules()->count() + 1);
        $this->due_date = now()->toDateString();
        $this->scheduled_amount = '0';
        $this->status = ContractPaymentScheduleStatus::Pending->value();
        $this->showFormModal = false;
    }
}
