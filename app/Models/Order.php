<?php

namespace App\Models;

use App\Concerns\AuditableWithContext;
use App\Concerns\BelongsToActiveCompany;
use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Order extends Model implements Auditable, HasMedia
{
    /** @use HasFactory<OrderFactory> */
    use AuditableWithContext, BelongsToActiveCompany, HasFactory, InteractsWithMedia;

    protected $table = 'orders';

    protected $fillable = [
        'company_id',
        'work_project_id',
        'requirement_id',
        'supplier_id',
        'supplier_quotation_id',
        'code',
        'order_type',
        'issue_date',
        'currency',
        'subtotal',
        'tax',
        'total',
        'status',
        'conditions',
        'observation',
        'approved_by',
        'approved_at',
        'approval_notes',
        'cancelled_by',
        'cancelled_at',
        'cancellation_reason',
    ];

    protected function casts(): array
    {
        return [
            'issue_date' => 'date',
            'subtotal' => 'decimal:2',
            'tax' => 'decimal:2',
            'total' => 'decimal:2',
            'approved_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'work_project_id');
    }

    public function requirement(): BelongsTo
    {
        return $this->belongsTo(Requirement::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(SupplierQuotation::class, 'supplier_quotation_id');
    }

    public function approvedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function cancelledByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function conformity(): HasOne
    {
        return $this->hasOne(OrderConformity::class);
    }

    public function accountsPayable(): HasOne
    {
        return $this->hasOne(AccountsPayable::class);
    }

    public function contract(): HasOne
    {
        return $this->hasOne(SupplierContract::class, 'order_id');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('attachments');
        $this->addMediaCollection('evidencias');
    }
}
