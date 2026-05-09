<?php

namespace App\Models;

use App\Concerns\AuditableWithContext;
use App\Concerns\BelongsToActiveCompany;
use Database\Factories\SupplierContractFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class SupplierContract extends Model implements Auditable, HasMedia
{
    /** @use HasFactory<SupplierContractFactory> */
    use AuditableWithContext, BelongsToActiveCompany, HasFactory, InteractsWithMedia;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'company_id',
        'work_project_id',
        'supplier_id',
        'purchase_order_id',
        'contract_number',
        'contract_type',
        'start_date',
        'end_date',
        'total_amount',
        'currency',
        'payment_conditions',
        'penalties',
        'guarantees',
        'status',
        'observation',
        'approved_by',
        'approved_at',
        'approval_notes',
        'cancelled_by',
        'cancelled_at',
        'cancellation_reason',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'total_amount' => 'decimal:2',
            'approved_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'work_project_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function approvedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function cancelledByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('signed_contract');
        $this->addMediaCollection('attachments');
    }

    public function paymentSchedules(): HasMany
    {
        return $this->hasMany(ContractPaymentSchedule::class)->orderBy('installment_number');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SupplierPayment::class)->latest('payment_date');
    }

    public function relatedPaymentsCount(): int
    {
        return $this->payments()->count();
    }

    public function totalPaid(): float
    {
        return (float) ($this->payments()->sum('amount') ?? 0);
    }

    public function pendingBalance(): float
    {
        return max(0, (float) $this->total_amount - $this->totalPaid());
    }
}
