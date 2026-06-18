<?php

use Database\Seeders\SiteContentSeeder;

beforeEach(function () {
    $this->seed(SiteContentSeeder::class);
});

test('returns a successful response', function () {
    $response = $this->get(route('home'));

    $response->assertOk();
    $response->assertSee('Construimos infraestructura que impulsa el desarrollo del Perú');
    $response->assertDontSee(__('Sign up'), false);
    $response->assertDontSee(__('Register'), false);
});
