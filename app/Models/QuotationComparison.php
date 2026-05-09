<?php

namespace App\Models;

use App\Concerns\AuditableWithContext;
use App\Concerns\BelongsToActiveCompany;
use Database\Factories\QuotationComparisonFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Contracts\Auditable;

class QuotationComparison extends Model implements Auditable
{
    /** @use HasFactory<QuotationComparisonFactory> */
    use AuditableWithContext, BelongsToActiveCompany, HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'company_id',
        'work_project_id',
        'purchase_request_id',
        'comparison_code',
        'selected_supplier_quotation_id',
        'selected_by',
        'compared_at',
        'selection_reason',
        'purchase_order_code',
        'purchase_order_generated_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'compared_at' => 'datetime',
            'purchase_order_generated_at' => 'datetime',
        ];
    }

    public function purchaseRequest(): BelongsTo
    {
        return $this->belongsTo(PurchaseRequest::class);
    }

    public function selectedQuotation(): BelongsTo
    {
        return $this->belongsTo(SupplierQuotation::class, 'selected_supplier_quotation_id');
    }

    public function selectedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'selected_by');
    }
}
