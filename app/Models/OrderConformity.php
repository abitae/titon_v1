<?php

namespace App\Models;

use App\Concerns\AuditableWithContext;
use App\Concerns\BelongsToActiveCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class OrderConformity extends Model implements Auditable, HasMedia
{
    use AuditableWithContext, BelongsToActiveCompany, InteractsWithMedia;

    protected $fillable = [
        'company_id',
        'order_id',
        'work_project_id',
        'responsible_user_id',
        'conformity_date',
        'result',
        'observation',
    ];

    protected function casts(): array
    {
        return [
            'conformity_date' => 'date',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function responsible(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('evidencias');
    }
}
