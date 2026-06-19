<?php

use App\Models\SiteSetting;
use App\Services\Frontend\SiteContentService;
use Database\Seeders\SiteContentSeeder;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->seed(SiteContentSeeder::class);
    Storage::fake('public');
});

test('home page displays seeded hero content', function () {
    $response = $this->get(route('home'));

    $response->assertOk();
    $response->assertSee('Construimos infraestructura que impulsa el desarrollo del Perú');
    $response->assertSee('Nosotros');
    $response->assertSee('Proyectos');
    $response->assertSee('Contacto');
});

test('frontend header displays mail access options', function () {
    $response = $this->get(route('home'));

    $response->assertOk();
    $response->assertSee('data-test="frontend-mail-access-trigger"', false);
    $response->assertSee('Acceso al correo');
    $response->assertSee(config('frontend.webmail_url'), false);
    $response->assertSee('data-test="frontend-webmail-link"', false);
    $response->assertSee('data-test="frontend-outlook-manual-trigger"', false);
    $response->assertSee(config('frontend.mail_host'));
    $response->assertSee('Outlook de escritorio');
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

test('frontend pages display header section images', function (string $routeName, string $sectionKey, string $imagePath) {
    $setting = SiteSetting::query()->where('key', $sectionKey)->firstOrFail();

    Storage::disk('public')->put($imagePath, 'fake-header-image');

    $setting->update(['image_path' => $imagePath]);

    app(SiteContentService::class)->forgetSection($sectionKey);

    $this->get(route($routeName))
        ->assertOk()
        ->assertSee(Storage::disk('public')->url($imagePath), false);
})->with([
    'about' => ['frontend.about', 'about.header', 'site/about-header.jpg'],
    'projects' => ['frontend.projects', 'projects.header', 'site/projects-header.jpg'],
    'contact' => ['frontend.contact', 'contact.header', 'site/contact-header.jpg'],
]);
