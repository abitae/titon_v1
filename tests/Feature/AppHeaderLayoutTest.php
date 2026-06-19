<?php

use App\Models\User;
use Illuminate\Support\Facades\Blade;

test('app header layout renders responsive navigation regions', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $html = Blade::render('<x-layouts::app.header><p>Contenido</p></x-layouts::app.header>');

    expect($html)
        ->toContain('Contenido')
        ->toContain('lg:hidden')
        ->toContain('max-lg:hidden')
        ->toContain('data-test="sidebar-menu-button"')
        ->toContain('data-test="logout-button"');
});
