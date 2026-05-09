<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;

class SaveUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $user = $this->route('user');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user)],
            'password' => [$this->isMethod('post') ? 'required' : 'nullable', 'confirmed', Password::default()],
            'company_ids' => ['required', 'array', 'min:1'],
            'company_ids.*' => ['integer', 'exists:companies,id'],
            'role_ids' => ['required', 'array'],
            'default_company_id' => ['nullable', 'integer', 'exists:companies,id'],
            'active_company_ids' => ['nullable', 'array'],
            'active_company_ids.*' => ['integer', 'exists:companies,id'],
        ];
    }

    /**
     * @return array<int, \Closure(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $companyIds = collect($this->input('company_ids', []))
                    ->map(fn (mixed $companyId): int => (int) $companyId)
                    ->unique()
                    ->values();

                $roleIds = collect($this->input('role_ids', []));
                $activeCompanyIds = collect($this->input('active_company_ids', []))
                    ->map(fn (mixed $companyId): int => (int) $companyId)
                    ->intersect($companyIds);

                if ($activeCompanyIds->isEmpty()) {
                    $validator->errors()->add('active_company_ids', 'Debes activar al menos una empresa para el usuario.');
                }

                foreach ($companyIds as $companyId) {
                    $roleId = $roleIds->get($companyId);

                    if ($roleId === null || ! Role::query()->whereKey($roleId)->exists()) {
                        $validator->errors()->add("role_ids.$companyId", 'Cada empresa asignada debe tener un rol valido.');
                    }
                }

                $defaultCompanyId = $this->filled('default_company_id')
                    ? $this->integer('default_company_id')
                    : null;

                if ($defaultCompanyId !== null && ! $activeCompanyIds->contains($defaultCompanyId)) {
                    $validator->errors()->add('default_company_id', 'La empresa por defecto debe estar activa.');
                }
            },
        ];
    }
}
