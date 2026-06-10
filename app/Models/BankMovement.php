<?php

namespace App\Models;

use App\Concerns\AuditableWithContext;
use App\Concerns\BelongsToActiveCompany;
use App\Enums\BankMovementDirection;
use App\Enums\BankMovementType;
use Database\Factories\BankMovementFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use OwenIt\Auditing\Contracts\Auditable;

class BankMovement extends Model implements Auditable
{
    /** @use HasFactory<BankMovementFactory> */
    use AuditableWithContext, BelongsToActiveCompany, HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'company_id',
        'bank_account_id',
        'movement_code',
        'direction',
        'type',
        'amount',
        'currency',
        'balance_after',
        'movement_date',
        'concept',
        'reference',
        'payment_method_id',
        'operation_type_id',
        'operation_number',
        'source_type',
        'source_id',
        'created_by_user_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'balance_after' => 'decimal:2',
            'movement_date' => 'date',
        ];
    }

    /**
     * @return BelongsTo<BankAccount, $this>
     */
    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    /**
     * @return BelongsTo<CatalogItem, $this>
     */
    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(CatalogItem::class, 'payment_method_id');
    }

    /**
     * @return BelongsTo<CatalogItem, $this>
     */
    public function operationType(): BelongsTo
    {
        return $this->belongsTo(CatalogItem::class, 'operation_type_id');
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function source(): MorphTo
    {
        return $this->morphTo();
    }

    public function typeLabel(): string
    {
        foreach (BankMovementType::cases() as $type) {
            if ($type->value() === $this->type) {
                return $type->label();
            }
        }

        return $this->type;
    }

    public function directionLabel(): string
    {
        foreach (BankMovementDirection::cases() as $direction) {
            if ($direction->value() === $this->direction) {
                return $direction->label();
            }
        }

        return $this->direction;
    }
}
