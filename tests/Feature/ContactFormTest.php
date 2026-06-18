<?php

use App\Models\ContactMessage;
use Database\Seeders\SiteContentSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(SiteContentSeeder::class);
});

test('contact form creates a message with valid data', function () {
    Livewire::test('frontend::contacto')
        ->set('name', 'Juan Pérez')
        ->set('email', 'juan@example.com')
        ->set('phone', '999888777')
        ->set('subject', 'Consulta de obra')
        ->set('message', 'Necesito información sobre sus servicios.')
        ->call('submit')
        ->assertHasNoErrors();

    $message = ContactMessage::query()->first();

    expect($message)->not->toBeNull();
    expect($message->name)->toBe('Juan Pérez');
    expect($message->email)->toBe('juan@example.com');
});

test('contact form rejects invalid email', function () {
    Livewire::test('frontend::contacto')
        ->set('name', 'Juan Pérez')
        ->set('email', 'correo-invalido')
        ->set('message', 'Mensaje de prueba.')
        ->call('submit')
        ->assertHasErrors(['email']);
});
