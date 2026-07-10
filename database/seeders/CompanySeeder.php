<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Company::query()->updateOrCreate(['ruc' => '20123456781'], [
            'name' => 'Titon Infraestructura',
            'business_name' => 'Titon Infraestructura S.A.C.',
            'correlative_prefix' => 'TITON',
            'address' => 'Av. Primavera 120, Lima',
            'phone' => '014445566',
            'email' => 'infraestructura@titon.pe',
            'logo' => null,
            'primary_color' => '#0f172a',
            'secondary_color' => '#0891b2',
            'status' => 'active',
        ]);

        Company::query()->updateOrCreate(['ruc' => '20123456782'], [
            'name' => 'Titon Proyectos',
            'business_name' => 'Titon Proyectos y Obras S.A.C.',
            'correlative_prefix' => 'TITON',
            'address' => 'Jr. Comercio 450, Arequipa',
            'phone' => '054456789',
            'email' => 'proyectos@titon.pe',
            'logo' => null,
            'primary_color' => '#1d4ed8',
            'secondary_color' => '#f59e0b',
            'status' => 'active',
        ]);

        Company::query()->updateOrCreate(['ruc' => '20123456783'], [
            'name' => 'Titon Servicios',
            'business_name' => 'Titon Servicios Integrales S.A.C.',
            'correlative_prefix' => 'TITON',
            'address' => 'Calle Los Olivos 780, Trujillo',
            'phone' => '044556677',
            'email' => 'servicios@titon.pe',
            'logo' => null,
            'primary_color' => '#065f46',
            'secondary_color' => '#7c3aed',
            'status' => 'active',
        ]);
    }
}
