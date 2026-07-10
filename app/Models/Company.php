<?php

namespace App\Models;

use App\Concerns\AuditableWithContext;
use Database\Factories\CompanyFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\Permission\Models\Role;

class Company extends Model implements Auditable
{
    /** @use HasFactory<CompanyFactory> */
    use AuditableWithContext, HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'correlative_prefix',
        'business_name',
        'ruc',
        'address',
        'phone',
        'email',
        'logo',
        'primary_color',
        'secondary_color',
        'status',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
        ];
    }

    /**
     * @return BelongsToMany<User, $this>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->using(CompanyUser::class)
            ->withPivot(['role_id', 'active', 'default_company'])
            ->withTimestamps();
    }

    /**
     * @return BelongsToMany<Role, $this>
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'company_user')
            ->withPivot(['user_id', 'active', 'default_company'])
            ->withTimestamps();
    }

    /**
     * @return HasMany<CompanyCorrelativeFormat, $this>
     */
    public function correlativeFormats(): HasMany
    {
        return $this->hasMany(CompanyCorrelativeFormat::class);
    }

    /**
     * @return HasOne<CompanyPdfSetting, $this>
     */
    public function pdfSetting(): HasOne
    {
        return $this->hasOne(CompanyPdfSetting::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function logoUrl(): ?string
    {
        if (blank($this->logo)) {
            return null;
        }

        if (filter_var($this->logo, FILTER_VALIDATE_URL)) {
            return $this->logo;
        }

        if (Storage::disk('public')->exists($this->logo)) {
            return Storage::disk('public')->url($this->logo);
        }

        return null;
    }
}
