<?php

namespace Database\Seeders;

use App\Models\ShowcaseProject;
use App\Models\SiteSetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SiteContentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sections = [
            [
                'key' => 'brand',
                'title' => config('app.name', 'Titon'),
                'subtitle' => null,
                'body' => null,
                'sort_order' => 0,
            ],
            [
                'key' => 'home.hero',
                'title' => 'Construimos infraestructura que impulsa el desarrollo del Perú',
                'subtitle' => 'Durante años, Titon se ha dedicado a ejecutar obras de infraestructura con calidad, seguridad y compromiso sostenible.',
                'body' => null,
                'cta_label' => 'Conócenos',
                'cta_url' => '/nosotros',
                'sort_order' => 1,
            ],
            [
                'key' => 'home.intro',
                'title' => 'Acerca de Titon',
                'subtitle' => null,
                'body' => 'Somos una empresa peruana especializada en infraestructura, obras civiles y servicios integrales. Nuestro equipo técnico y operativo trabaja con los más altos estándares de calidad para entregar proyectos que conectan comunidades y generan valor.',
                'cta_label' => 'Más información',
                'cta_url' => '/nosotros',
                'sort_order' => 2,
            ],
            [
                'key' => 'home.services',
                'title' => 'Servicios integrales de infraestructura',
                'subtitle' => 'Soluciones para cada etapa del proyecto',
                'body' => 'Desde la planificación hasta la ejecución y mantenimiento, ofrecemos servicios que cubren obras civiles, movimiento de tierras, pavimentación, estructuras y gestión de maquinaria.',
                'cta_label' => 'Ver proyectos',
                'cta_url' => '/proyectos',
                'sort_order' => 10,
            ],
            [
                'key' => 'home.cta',
                'title' => '¿Tienes un proyecto en mente?',
                'subtitle' => 'Nuestro equipo está listo para asesorarte.',
                'body' => null,
                'cta_label' => 'Contáctanos',
                'cta_url' => '/contacto',
                'sort_order' => 20,
            ],
            [
                'key' => 'home.cards.nosotros',
                'title' => 'Nosotros',
                'subtitle' => 'Historia, misión y valores corporativos.',
                'body' => null,
                'cta_label' => 'Más información',
                'cta_url' => '/nosotros',
                'sort_order' => 1,
            ],
            [
                'key' => 'home.cards.proyectos',
                'title' => 'Proyectos',
                'subtitle' => 'Portafolio de obras ejecutadas y en curso.',
                'body' => null,
                'cta_label' => 'Más información',
                'cta_url' => '/proyectos',
                'sort_order' => 2,
            ],
            [
                'key' => 'home.cards.servicios',
                'title' => 'Servicios',
                'subtitle' => 'Obras civiles, infraestructura y mantenimiento.',
                'body' => null,
                'cta_label' => 'Más información',
                'cta_url' => '/nosotros',
                'sort_order' => 3,
            ],
            [
                'key' => 'home.cards.contacto',
                'title' => 'Contacto',
                'subtitle' => 'Escríbenos y te responderemos a la brevedad.',
                'body' => null,
                'cta_label' => 'Más información',
                'cta_url' => '/contacto',
                'sort_order' => 4,
            ],
            [
                'key' => 'about.header',
                'title' => 'Nosotros',
                'subtitle' => 'Comprometidos con la infraestructura del Perú',
                'body' => null,
                'sort_order' => 1,
            ],
            [
                'key' => 'about.mission',
                'title' => 'Misión',
                'subtitle' => null,
                'body' => 'Ejecutar proyectos de infraestructura con excelencia técnica, seguridad y responsabilidad social, generando valor para nuestros clientes y las comunidades donde operamos.',
                'sort_order' => 2,
            ],
            [
                'key' => 'about.vision',
                'title' => 'Visión',
                'subtitle' => null,
                'body' => 'Ser referente en infraestructura en el Perú, reconocidos por la calidad de nuestras obras, la innovación operativa y el compromiso con la sostenibilidad.',
                'sort_order' => 3,
            ],
            [
                'key' => 'about.values',
                'title' => 'Valores',
                'subtitle' => null,
                'body' => 'Integridad, seguridad, calidad, trabajo en equipo y mejora continua guían cada decisión en Titon.',
                'sort_order' => 4,
            ],
            [
                'key' => 'about.history',
                'title' => 'Nuestra historia',
                'subtitle' => null,
                'body' => 'Titon nació con la convicción de que la infraestructura es el motor del desarrollo. Hemos crecido junto a clientes públicos y privados, ejecutando obras en distintas regiones del país con equipos especializados y maquinaria de alto rendimiento.',
                'sort_order' => 5,
            ],
            [
                'key' => 'about.stats.years',
                'title' => '15+',
                'subtitle' => 'Años de experiencia',
                'body' => null,
                'sort_order' => 1,
            ],
            [
                'key' => 'about.stats.projects',
                'title' => '80+',
                'subtitle' => 'Proyectos ejecutados',
                'body' => null,
                'sort_order' => 2,
            ],
            [
                'key' => 'about.stats.cities',
                'title' => '12',
                'subtitle' => 'Ciudades atendidas',
                'body' => null,
                'sort_order' => 3,
            ],
            [
                'key' => 'projects.header',
                'title' => 'Proyectos',
                'subtitle' => 'Obras que transforman territorios y conectan comunidades',
                'body' => null,
                'sort_order' => 1,
            ],
            [
                'key' => 'contact.header',
                'title' => 'Contacto',
                'subtitle' => 'Estamos listos para escuchar tu próximo proyecto',
                'body' => null,
                'sort_order' => 1,
            ],
            [
                'key' => 'contact.info',
                'title' => 'Titon Infraestructura',
                'subtitle' => 'Av. Primavera 120, Lima',
                'body' => "Teléfono: 01 444 5566\nCorreo: infraestructura@titon.pe\nHorario: Lun–Vie 8:00–18:00",
                'sort_order' => 2,
            ],
            [
                'key' => 'footer.social',
                'title' => 'Síguenos',
                'subtitle' => null,
                'body' => null,
                'sort_order' => 1,
            ],
        ];

        foreach ($sections as $section) {
            SiteSetting::query()->updateOrCreate(
                ['key' => $section['key']],
                [
                    ...$section,
                    'is_active' => true,
                ],
            );
        }

        $projects = [
            [
                'title' => 'Ampliación vía costera Norte',
                'city' => 'Lima',
                'client_name' => 'Municipalidad Provincial',
                'summary' => 'Pavimentación y señalización de 12 km de vía urbana con mejoras de drenaje pluvial.',
            ],
            [
                'title' => 'Puente modular Río Chili',
                'city' => 'Arequipa',
                'client_name' => 'Gobierno Regional',
                'summary' => 'Construcción de puente vehicular que conecta dos distritos agrícolas de la región.',
            ],
            [
                'title' => 'Planta de tratamiento ETAR',
                'city' => 'Trujillo',
                'client_name' => 'EPS Local',
                'summary' => 'Infraestructura sanitaria con capacidad ampliada para 50,000 habitantes.',
            ],
        ];

        foreach ($projects as $index => $project) {
            ShowcaseProject::query()->updateOrCreate(
                ['slug' => Str::slug($project['title'])],
                [
                    ...$project,
                    'description' => $project['summary'].' Ejecutado con estándares de calidad Titon y supervisión técnica permanente.',
                    'is_published' => true,
                    'is_featured' => $index === 0,
                    'sort_order' => $index + 1,
                    'published_at' => now()->subMonths($index + 1),
                ],
            );
        }
    }
}
