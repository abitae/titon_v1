<?php

use App\Models\ContactMessage;
use App\Services\Frontend\SiteContentService;
use Flux\Flux;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new
#[Layout('layouts::frontend')]
#[Title('Contacto')]
class extends Component
{
    public ?object $header = null;

    public ?object $contactInfo = null;

    public string $name = '';

    public string $email = '';

    public string $phone = '';

    public string $subject = '';

    public string $message = '';

    public function mount(SiteContentService $content): void
    {
        $this->header = $content->section('contact.header');
        $this->contactInfo = $content->section('contact.info');
    }

    public function submit(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'subject' => ['nullable', 'string', 'max:150'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        ContactMessage::query()->create($validated);

        $this->reset(['name', 'email', 'phone', 'subject', 'message']);

        Flux::toast(text: 'Mensaje enviado correctamente. Nos pondremos en contacto contigo pronto.');
    }
};
?>

<div>
    @if ($header)
        <x-frontend.page-header :title="$header->title" :subtitle="$header->subtitle" />
    @endif

    <section class="bg-white py-16">
        <div class="mx-auto grid max-w-7xl gap-12 px-4 lg:grid-cols-2 lg:px-8">
            <div>
                <h2 class="text-xl font-bold text-slate-900">Información de contacto</h2>
                @if ($contactInfo)
                    <div class="mt-6 space-y-4 text-slate-600">
                        @if ($contactInfo->title)
                            <p class="font-semibold text-slate-900">{{ $contactInfo->title }}</p>
                        @endif
                        @if ($contactInfo->subtitle)
                            <p class="flex items-start gap-2">
                                <flux:icon name="map-pin" class="mt-0.5 size-5 shrink-0 text-cyan-700" />
                                {{ $contactInfo->subtitle }}
                            </p>
                        @endif
                        @if ($contactInfo->body)
                            @foreach (explode("\n", $contactInfo->body) as $line)
                                <p>{{ $line }}</p>
                            @endforeach
                        @endif
                    </div>
                @endif
            </div>

            <div class="rounded-xl border border-slate-200 bg-slate-50 p-6 lg:p-8">
                <h2 class="text-xl font-bold text-slate-900">Envíanos un mensaje</h2>
                <form wire:submit="submit" class="mt-6 space-y-5">
                    <flux:input wire:model="name" label="Nombre" required />
                    <flux:input wire:model="email" type="email" label="Correo electrónico" required />
                    <flux:input wire:model="phone" label="Teléfono" />
                    <flux:input wire:model="subject" label="Asunto" />
                    <flux:textarea wire:model="message" label="Mensaje" rows="5" required />
                    <flux:button type="submit" variant="primary" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="submit">Enviar mensaje</span>
                        <span wire:loading wire:target="submit">Enviando...</span>
                    </flux:button>
                </form>
            </div>
        </div>
    </section>
</div>
