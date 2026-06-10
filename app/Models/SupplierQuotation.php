<?php

namespace App\Models;

use App\Concerns\AuditableWithContext;
use App\Concerns\BelongsToActiveCompany;
use App\Enums\QuotationCaptureMode;
use Database\Factories\SupplierQuotationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class SupplierQuotation extends Model implements Auditable, HasMedia
{
    /** @use HasFactory<SupplierQuotationFactory> */
    use AuditableWithContext, BelongsToActiveCompany, HasFactory, InteractsWithMedia;

    protected $fillable = [
        'company_id',
        'work_project_id',
        'requirement_id',
        'supplier_id',
        'code',
        'quotation_number',
        'quotation_date',
        'valid_until',
        'currency',
        'subtotal',
        'tax',
        'total',
        'delivery_time_days',
        'payment_conditions',
        'warranty',
        'status',
        'observation',
        'capture_mode',
        'total_score',
    ];

    protected function casts(): array
    {
        return [
            'quotation_date' => 'date',
            'valid_until' => 'date',
            'subtotal' => 'decimal:2',
            'tax' => 'decimal:2',
            'total' => 'decimal:2',
            'total_score' => 'decimal:2',
        ];
    }

    public function requirement(): BelongsTo
    {
        return $this->belongsTo(Requirement::class);
    }

    /** @deprecated */
    public function purchaseRequest(): BelongsTo
    {
        return $this->requirement();
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SupplierQuotationItem::class);
    }

    public function scores(): HasMany
    {
        return $this->hasMany(QuotationScore::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('cotizacion_pdf')->singleFile();
        $this->addMediaCollection('ficha_tecnica');
        $this->addMediaCollection('certificados');
        $this->addMediaCollection('anexos');
        $this->addMediaCollection('imagenes');
    }

    public function isPdfCapture(): bool
    {
        return $this->capture_mode === QuotationCaptureMode::Pdf->value();
    }

    public function quotationPdfUrl(): ?string
    {
        return $this->getFirstMediaUrl('cotizacion_pdf') ?: null;
    }

    public function quotationPdfPreviewUrl(): string
    {
        return route('purchases.quotations.pdf', $this, absolute: false);
    }
}
