<?php

namespace App\Models;

use App\Concerns\AuditableWithContext;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable implements Auditable
{
    /** @use HasFactory<UserFactory> */
    use AuditableWithContext, HasFactory, HasRoles, Notifiable, TwoFactorAuthenticatable;

    public const SUPER_ADMIN_ROLE = 'Super Admin';

    protected string $guard_name = 'web';

    /**
     * @var list<string>
     */
    protected $auditExclude = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    /**
     * @return BelongsToMany<Company, $this>
     */
    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class)
            ->using(CompanyUser::class)
            ->withPivot(['role_id', 'active', 'default_company'])
            ->withTimestamps();
    }

    /**
     * @return BelongsToMany<Company, $this>
     */
    public function activeCompanies(): BelongsToMany
    {
        return $this->companies()->wherePivot('active', true);
    }

    public function isSuperAdmin(): bool
    {
        if ($this->getKey() === null) {
            return false;
        }

        if ($this->hasRole(self::SUPER_ADMIN_ROLE)) {
            return true;
        }

        $roleId = static::superAdminRoleId();

        if ($roleId === null) {
            return false;
        }

        if ($this->relationLoaded('companies')) {
            $hasPivotRole = $this->companies->contains(
                fn (Company $company): bool => (int) $company->pivot->role_id === $roleId,
            );
        } else {
            $hasPivotRole = $this->companies()->wherePivot('role_id', $roleId)->exists();
        }

        if ($hasPivotRole) {
            return true;
        }

        return DB::table(config('permission.table_names.model_has_roles'))
            ->where('role_id', $roleId)
            ->where('model_id', $this->getKey())
            ->where('model_type', $this->getMorphClass())
            ->exists();
    }

    /**
     * @param  Builder<User>  $query
     * @return Builder<User>
     */
    public function scopeWithoutSuperAdmins(Builder $query): Builder
    {
        $roleId = static::superAdminRoleId();

        if ($roleId === null) {
            return $query;
        }

        $modelType = $this->getMorphClass();
        $rolesTable = config('permission.table_names.model_has_roles');
        $qualifiedKeyName = $query->getModel()->getQualifiedKeyName();

        return $query
            ->whereNotIn($qualifiedKeyName, function ($sub) use ($roleId): void {
                $sub->select('user_id')
                    ->from('company_user')
                    ->where('role_id', $roleId);
            })
            ->whereNotIn($qualifiedKeyName, function ($sub) use ($roleId, $modelType, $rolesTable): void {
                $sub->select('model_id')
                    ->from($rolesTable)
                    ->where('role_id', $roleId)
                    ->where('model_type', $modelType);
            });
    }

    public static function superAdminRoleId(): ?int
    {
        $id = Role::query()
            ->where('name', self::SUPER_ADMIN_ROLE)
            ->where('guard_name', 'web')
            ->value('id');

        return $id !== null ? (int) $id : null;
    }

    protected function getDefaultGuardName(): string
    {
        return $this->guard_name;
    }
}
