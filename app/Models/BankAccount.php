<?php

namespace App\Models;

use App\Concerns\AuditableWithContext;
use App\Concerns\BelongsToActiveCompany;
use Database\Factories\BankAccountFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Contracts\Auditable;

class BankAccount extends Model implements Auditable
{
    /** @use HasFactory<BankAccountFactory> */
    use AuditableWithContext, BelongsToActiveCompany, HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'company_id',
        'catalog_bank_id',
        'name',
        'account_number',
        'currency',
        'balance',
        'is_cash',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'balance' => 'decimal:2',
            'is_cash' => 'bool',
            'is_active' => 'bool',
        ];
    }

    /**
     * @return BelongsTo<CatalogItem, $this>
     */
    public function institution(): BelongsTo
    {
        return $this->belongsTo(CatalogItem::class, 'catalog_bank_id');
    }

    /**
     * @return HasMany<BankMovement, $this>
     */
    public function movements(): HasMany
    {
        return $this->hasMany(BankMovement::class);
    }

    public function displayLabel(): string
    {
        if ($this->is_cash) {
            return $this->name;
        }

        $institution = $this->institution?->name;
        $parts = array_filter([$this->name, $institution, $this->account_number]);

        return implode(' · ', $parts);
    }

    /**
     * @return HasMany<AccountsPayablePayment, $this>
     */
    public function accountsPayablePayments(): HasMany
    {
        return $this->hasMany(AccountsPayablePayment::class);
    }

    public function scopeAvailableForPayment(Builder $query, string $currency, bool $requiresBankAccount): Builder
    {
        return $query
            ->where('is_active', true)
            ->where('currency', $currency)
            ->when(
                $requiresBankAccount,
                fn (Builder $nested): Builder => $nested->where('is_cash', false),
                fn (Builder $nested): Builder => $nested->where('is_cash', true),
            )
            ->orderBy('name');
    }
}
