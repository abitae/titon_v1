<?php

namespace App\Models;

use App\Concerns\AuditableWithContext;
use App\Concerns\BelongsToActiveCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class AccountsPayablePayment extends Model implements Auditable, HasMedia
{
    use AuditableWithContext, BelongsToActiveCompany, InteractsWithMedia;

    protected $fillable = [
        'company_id',
        'accounts_payable_id',
        'supplier_id',
        'work_project_id',
        'payment_date',
        'amount',
        'currency',
        'payment_method_id',
        'bank_id',
        'operation_type_id',
        'operation_number',
        'paid_by',
        'concept',
        'observation',
    ];

    protected function casts(): array
    {
        return [
            'payment_date' => 'date',
            'amount' => 'decimal:2',
        ];
    }

    public function accountsPayable(): BelongsTo
    {
        return $this->belongsTo(AccountsPayable::class);
    }

    public function payer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('comprobante');
    }
}
