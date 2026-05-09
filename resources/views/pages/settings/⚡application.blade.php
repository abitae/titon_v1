<?php

use App\Services\Application\ApplicationSettingsManager;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

new #[Title('Application settings')] class extends Component {
    use WithFileUploads;

    public string $application_name = '';

    public ?TemporaryUploadedFile $logo = null;

    public ?string $currentLogoUrl = null;

    public function mount(ApplicationSettingsManager $applicationSettings): void
    {
        abort_unless(Auth::user()?->hasRole('Super Admin'), 403);

        $settings = $applicationSettings->current();

        $this->application_name = $settings->application_name;
        $this->currentLogoUrl = $settings->logoUrl();
    }

    public function saveApplicationSettings(ApplicationSettingsManager $applicationSettings): void
    {
        abort_unless(Auth::user()?->hasRole('Super Admin'), 403);

        $validated = $this->validate([
            'application_name' => ['required', 'string', 'max:120'],
            'logo' => ['nullable', 'image', 'max:2048'],
        ]);

        $settings = $applicationSettings->current();
        $logoPath = $settings->logo_path;

        if ($this->logo instanceof TemporaryUploadedFile) {
            if (filled($logoPath) && Storage::disk('public')->exists($logoPath)) {
                Storage::disk('public')->delete($logoPath);
            }

            $logoPath = $this->logo->store('application-settings', 'public');
        }

        $applicationSettings->update([
            'application_name' => $validated['application_name'],
            'logo_path' => $logoPath,
        ]);

        $this->logo = null;
        $this->currentLogoUrl = $applicationSettings->logoUrl();

        Flux::toast(variant: 'success', text: __('Application settings updated.'));
    }

    public function removeLogo(ApplicationSettingsManager $applicationSettings): void
    {
        abort_unless(Auth::user()?->hasRole('Super Admin'), 403);

        $applicationSettings->removeLogo();

        $this->logo = null;
        $this->currentLogoUrl = null;

        Flux::toast(variant: 'success', text: __('Application logo removed.'));
    }
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading class="sr-only">{{ __('Application settings') }}</flux:heading>

    <x-pages::settings.layout :heading="__('Application')" :subheading="__('Manage the global identity of the platform')">
        <form wire:submit="saveApplicationSettings" class="my-6 w-full space-y-6">
            <div class="rounded-3xl border border-slate-200 bg-slate-50/80 p-4 dark:border-slate-800 dark:bg-slate-900/70">
                <div class="flex items-start gap-4">
                    <div class="flex size-20 shrink-0 items-center justify-center overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-950">
                        @if ($logo)
                            <img src="{{ $logo->temporaryUrl() }}" alt="{{ __('Temporary logo preview') }}" class="size-full object-cover" />
                        @elseif ($currentLogoUrl)
                            <img src="{{ $currentLogoUrl }}" alt="{{ $application_name }}" class="size-full object-cover" />
                        @else
                            <x-app-logo-icon class="size-10 fill-current text-slate-700 dark:text-slate-100" />
                        @endif
                    </div>

                    <div class="min-w-0 flex-1 space-y-3">
                        <div>
                            <flux:heading size="lg">{{ __('General identity') }}</flux:heading>
                            <flux:text class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                {{ __('Define the public name of the application and the shared logo used in the layout.') }}
                            </flux:text>
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            <flux:input wire:model="logo" :label="__('General logo')" type="file" accept="image/png,image/jpeg,image/webp,image/svg+xml" />

                            @if ($currentLogoUrl || $logo)
                                <flux:button type="button" variant="ghost" wire:click="removeLogo">
                                    {{ __('Remove logo') }}
                                </flux:button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <flux:input
                wire:model="application_name"
                :label="__('Application name')"
                type="text"
                required
                maxlength="120"
                autocomplete="organization"
            />

            <div class="flex items-center gap-4">
                <flux:button variant="primary" type="submit">
                    {{ __('Save') }}
                </flux:button>
            </div>
        </form>
    </x-pages::settings.layout>
</section>
