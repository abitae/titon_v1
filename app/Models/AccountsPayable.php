<?php

namespace App\Models;

use App\Concerns\AuditableWithContext;
use App\Concerns\BelongsToActiveCompany;
use Database\Factories\AccountsPayableFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Contracts\Auditable;

class AccountsPayable extends Model implements Auditable
{
    /** @use HasFactory<AccountsPayableFactory> */
    use AuditableWithContext, BelongsToActiveCompany, HasFactory;

    protected $table = 'accounts_payable';

    protected $fillable = [
        'company_id',
        'order_id',
        'supplier_id',
        'work_project_id',
        'code',
        'issue_date',
        'due_date',
        'currency',
        'amount',
        'paid_amount',
        'balance',
        'status',
        'observation',
    ];

    protected function casts(): array
    {
        return [
            'issue_date' => 'date',
            'due_date' => 'date',
            'amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'balance' => 'decimal:2',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'work_project_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(PayableDocument::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(AccountsPayablePayment::class);
    }

    public function requiredDocumentsUploaded(): bool
    {
        return ! $this->documents()
            ->where('required', true)
            ->where('uploaded', false)
            ->exists();
    }
}
