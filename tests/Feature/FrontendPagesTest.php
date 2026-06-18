<?php

use Database\Seeders\SiteContentSeeder;

beforeEach(function () {
    $this->seed(SiteContentSeeder::class);
});

test('home page displays seeded hero content', function () {
    $response = $this->get(route('home'));

    $response->assertOk();
    $response->assertSee('Construimos infraestructura que impulsa el desarrollo del Perú');
    $response->assertSee('Nosotros');
    $response->assertSee('Proyectos');
    $response->assertSee('Contacto');
});

test('about page displays mission content', function () {
    $response = $this->get(route('frontend.about'));

    $response->assertOk();
    $response->assertSee('Comprometidos con la infraestructura del Perú');
    $response->assertSee('Misión');
});

test('projects page displays showcase projects', function () {
    $response = $this->get(route('frontend.projects'));

    $response->assertOk();
    $response->assertSee('Ampliación vía costera Norte');
    $response->assertSee('Puente modular Río Chili');
});

test('contact page displays contact form', function () {
    $response = $this->get(route('frontend.contact'));

    $response->assertOk();
    $response->assertSee('Envíanos un mensaje');
    $response->assertSee('infraestructura@titon.pe');
});
