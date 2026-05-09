<?php

namespace App\Models;

use App\Concerns\AuditableWithContext;
use App\Concerns\BelongsToActiveCompany;
use Database\Factories\SupplierPaymentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class SupplierPayment extends Model implements Auditable, HasMedia
{
    /** @use HasFactory<SupplierPaymentFactory> */
    use AuditableWithContext, BelongsToActiveCompany, HasFactory, InteractsWithMedia;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'company_id',
        'work_project_id',
        'supplier_contract_id',
        'registry_code',
        'supplier_id',
        'contract_payment_schedule_id',
        'payment_date',
        'amount',
        'currency',
        'operation_type_id',
        'payment_method_id',
        'bank_id',
        'operation_number',
        'responsible_user_id',
        'concept',
        'observation',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'payment_date' => 'date',
            'amount' => 'decimal:2',
        ];
    }

    public function supplierContract(): BelongsTo
    {
        return $this->belongsTo(SupplierContract::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(ContractPaymentSchedule::class, 'contract_payment_schedule_id');
    }

    public function operationType(): BelongsTo
    {
        return $this->belongsTo(CatalogItem::class, 'operation_type_id');
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(CatalogItem::class, 'payment_method_id');
    }

    public function bank(): BelongsTo
    {
        return $this->belongsTo(CatalogItem::class, 'bank_id');
    }

    public function responsibleUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'work_project_id');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('voucher');
    }
}
