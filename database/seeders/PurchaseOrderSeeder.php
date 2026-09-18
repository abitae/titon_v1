<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Project;
use App\Models\Requirement;
use App\Models\Supplier;
use App\Models\User;
use Database\Seeders\Support\CompletePurchaseFlow;
use Illuminate\Database\Seeder;

class PurchaseOrderSeeder extends Seeder
{
    public function run(): void
    {
        $flow = app(CompletePurchaseFlow::class);

        Company::query()->each(function (Company $company) use ($flow): void {
            $project = Project::query()->where('company_id', $company->id)->orderBy('id')->first();
            $responsible = $company->users()->wherePivot('active', true)->first()
                ?? User::query()->first();
            $suppliers = Supplier::query()->where('company_id', $company->id)->orderBy('id')->take(2)->get();

            if ($project === null || $responsible === null || $suppliers->count() < 1) {
                return;
            }

            if (! Requirement::query()->where('company_id', $company->id)->where('title', 'Suministro de agregados demo')->exists()) {
                $flow->createOrder($company, $project, $responsible, $suppliers, [
                    'title' => 'Suministro de agregados demo',
                    'requirement_type' => 'material',
                    'cost_type_code' => 'MAT',
                    'priority' => 'alta',
                    'description' => 'Arena gruesa y piedra chancada para concretos de cimentacion.',
                    'observation' => 'Verificar granulometria en laboratorio de obra.',
                    'items' => [
                        [
                            'item_type' => 'material',
                            'cost_center_ua' => 'UA-CIV-02',
                            'description' => 'Arena gruesa lavada',
                            'unit' => 'm3',
                            'quantity' => '40',
                            'technical_specification' => 'Modulo de fineza 2.6 a 3.1',
                            'estimated_unit_price' => '75',
                            'observation' => 'Entrega en acopio norte.',
                        ],
                        [
                            'item_type' => 'material',
                            'cost_center_ua' => 'UA-CIV-02',
                            'description' => 'Piedra chancada 1/2"',
                            'unit' => 'm3',
                            'quantity' => '35',
                            'technical_specification' => 'Agregado grueso triturado, maximo 12.5 mm',
                            'estimated_unit_price' => '92',
                            'observation' => 'Sin finos excesivos.',
                        ],
                    ],
                    'payment_conditions' => 'Pago a 21 dias. Flete incluido hasta acopio de obra.',
                    'warranty' => 'Reposición por material fuera de especificacion',
                    'quotation_observation' => 'Produccion propia. Entrega programada en 6 dias.',
                    'order_conditions' => 'Pago a 21 dias calendario. Entrega en acopio norte, incluye flete e IGV.',
                    'order_observation' => 'Coordinar ingreso de volquetes con seguridad de obra. Horario 7:00 a 16:00.',
                    'item_observations' => [
                        'Verificar modulo de fineza antes de descargar.',
                        'Rechazar si el porcentaje de finos supera el limite.',
                    ],
                    'approve' => true,
                    'approval_notes' => 'Aprobada por gerencia. Priorizar primera descarga el lunes.',
                ]);
            }

            if (! Requirement::query()->where('company_id', $company->id)->where('title', 'EPP y señaletica demo')->exists()) {
                $flow->createOrder($company, $project, $responsible, $suppliers, [
                    'title' => 'EPP y señaletica demo',
                    'requirement_type' => 'material',
                    'cost_type_code' => 'MAT',
                    'priority' => 'media',
                    'description' => 'Equipos de proteccion personal y señaletica temporal de obra.',
                    'observation' => 'Pedido reemplazado por convenio marco.',
                    'items' => [
                        [
                            'item_type' => 'material',
                            'cost_center_ua' => 'UA-SSO-01',
                            'description' => 'Casco de seguridad clase B',
                            'unit' => 'und',
                            'quantity' => '50',
                            'technical_specification' => 'ANSI Z89.1, diadema ajustable',
                            'estimated_unit_price' => '28',
                            'observation' => 'Color amarillo.',
                        ],
                        [
                            'item_type' => 'material',
                            'cost_center_ua' => 'UA-SSO-01',
                            'description' => 'Malla de seguridad naranja',
                            'unit' => 'm',
                            'quantity' => '200',
                            'technical_specification' => 'Malla 1.20 m de alto, UV',
                            'estimated_unit_price' => '3.80',
                            'observation' => 'Incluye grapas.',
                        ],
                    ],
                    'payment_conditions' => 'Contado contra entrega.',
                    'warranty' => '90 dias por defectos de fabricacion',
                    'quotation_observation' => 'Stock en Lima. Entrega en 48 horas.',
                    'order_conditions' => 'Contado contra entrega en almacén de obra.',
                    'order_observation' => 'Orden anulada porque ingreso un convenio marco de EPP.',
                    'item_observations' => [
                        'Talla única, diadema textil.',
                        'Rollos de 50 m.',
                    ],
                    'approve' => true,
                    'approval_notes' => 'Aprobada inicialmente para cubir el faltante de EPP.',
                    'cancel' => true,
                    'cancellation_reason' => 'Reemplazada por convenio marco de seguridad y salud en el trabajo.',
                ]);
            }
        });
    }
}
