<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        Company::query()->each(function (Company $company): void {
            Supplier::withoutGlobalScopes()
                ->where('company_id', $company->id)
                ->get()
                ->each(function (Supplier $supplier, int $index) use ($company): void {
                    $supplier->fill([
                        'commercial_name' => $supplier->commercial_name ?: 'Marca '.$supplier->business_name,
                        'contact_name' => $supplier->contact_name ?: 'Contacto comercial',
                        'phone' => $supplier->phone ?: '999888777',
                        'email' => $supplier->email ?: 'compras'.$company->id.'-'.$supplier->id.'@demo.test',
                        'address' => $supplier->address ?: 'Av. Industriales '.($index + 100).', Lima',
                        'city' => $supplier->city ?: 'Lima',
                        'bank_name' => $supplier->bank_name ?: 'BCP',
                        'bank_account' => $supplier->bank_account ?: sprintf('191-%08d-0-12', $supplier->id),
                        'cci' => $supplier->cci ?: sprintf('00219100%010d12', $supplier->id),
                        'status' => $supplier->status ?: 'active',
                    ])->save();
                });

            Supplier::withoutGlobalScopes()->updateOrCreate(
                [
                    'company_id' => $company->id,
                    'ruc' => sprintf('20%02d%07d', $company->id, 3),
                ],
                [
                    'business_name' => 'Proveedor Gamma '.$company->id,
                    'commercial_name' => 'Gamma Supply',
                    'contact_name' => 'Lucia Mendoza',
                    'phone' => '987654321',
                    'email' => 'proveedor'.$company->id.'-3@demo.test',
                    'address' => 'Av. Argentina 2450, Callao',
                    'city' => 'Callao',
                    'bank_name' => 'BBVA',
                    'bank_account' => sprintf('0011-%08d-00', $company->id),
                    'cci' => sprintf('01100110%010d00', $company->id),
                    'status' => 'active',
                ],
            );
        });
    }
}
