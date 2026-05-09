<?php

namespace App\Livewire\Settings;

use App\Actions\Companies\ResolveCurrentCompany;
use App\Concerns\InteractsWithToast;
use App\Enums\CorrelativeSubject;
use App\Models\CompanyCorrelativeFormat;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class ManageCorrelativeFormats extends Component
{
    use InteractsWithToast;

    public string $title = 'Correlativos';

    /**
     * @var array<int|string, array{suffix: string, template: string, pad_length: int}>
     */
    public array $draft = [];

    public function mount(ResolveCurrentCompany $resolveCurrentCompany): void
    {
        abort_unless(auth()->user()?->can('catalogs.editar'), 403);
        $company = $resolveCurrentCompany->handle(auth()->user());
        abort_if($company === null, 403);

        foreach (CorrelativeSubject::cases() as $subject) {
            $defaults = $subject->defaultFormat();
            CompanyCorrelativeFormat::query()->firstOrCreate(
                [
                    'company_id' => $company->id,
                    'subject' => $subject->value,
                    'series' => '',
                ],
                [
                    'suffix' => $defaults['suffix'],
                    'template' => $defaults['template'],
                    'pad_length' => $defaults['pad_length'],
                    'is_active' => true,
                ],
            );
        }

        $this->hydrateDraft($company->id);
    }

    public function render(ResolveCurrentCompany $resolveCurrentCompany): View
    {
        abort_unless(auth()->user()?->can('catalogs.ver'), 403);

        $company = $resolveCurrentCompany->handle(auth()->user());
        abort_if($company === null, 403);

        $formats = CompanyCorrelativeFormat::query()
            ->where('company_id', $company->id)
            ->where('series', '')
            ->orderBy('subject')
            ->get();

        return view('livewire.settings.manage-correlative-formats', [
            'company' => $company,
            'formats' => $formats,
        ])->layout('layouts.app', ['title' => $this->title]);
    }

    public function saveRow(int $formatId, ResolveCurrentCompany $resolveCurrentCompany): void
    {
        abort_unless(auth()->user()?->can('catalogs.editar'), 403);

        $company = $resolveCurrentCompany->handle(auth()->user());
        abort_if($company === null, 403);

        abort_if(! isset($this->draft[$formatId]) || ! is_array($this->draft[$formatId]), 403);

        $validated = $this->validate([
            'draft.'.$formatId.'.suffix' => ['required', 'string', 'max:24'],
            'draft.'.$formatId.'.template' => ['required', 'string', 'max:255'],
            'draft.'.$formatId.'.pad_length' => ['required', 'integer', 'min:1', 'max:12'],
        ]);
        $draft = $validated['draft'][$formatId];

        /** @var CompanyCorrelativeFormat $row */
        $row = CompanyCorrelativeFormat::query()
            ->where('company_id', $company->id)
            ->whereKey($formatId)
            ->firstOrFail();

        $row->update([
            'suffix' => $draft['suffix'],
            'template' => $draft['template'],
            'pad_length' => $draft['pad_length'],
        ]);

        $this->hydrateDraft($company->id);
        $this->successToast('Formato guardado.');
    }

    protected function hydrateDraft(int $companyId): void
    {
        $this->draft = CompanyCorrelativeFormat::query()
            ->where('company_id', $companyId)
            ->where('series', '')
            ->get()
            ->mapWithKeys(fn (CompanyCorrelativeFormat $format): array => [
                $format->id => [
                    'suffix' => $format->suffix,
                    'template' => $format->template,
                    'pad_length' => (int) $format->pad_length,
                ],
            ])
            ->all();
    }
}
