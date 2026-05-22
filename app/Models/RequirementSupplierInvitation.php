<?php

namespace App\Models;

use App\Concerns\AuditableWithContext;
use App\Concerns\BelongsToActiveCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Contracts\Auditable;

class RequirementSupplierInvitation extends Model implements Auditable
{
    use AuditableWithContext, BelongsToActiveCompany;

    protected $fillable = [
        'company_id',
        'requirement_id',
        'supplier_id',
        'sent_by',
        'sent_at',
        'status',
        'message',
        'response_deadline',
        'observation',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
            'response_deadline' => 'date',
        ];
    }

    public function requirement(): BelongsTo
    {
        return $this->belongsTo(Requirement::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_by');
    }
}
