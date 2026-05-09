<?php

namespace App\Services\Correlatives;

use App\Enums\CorrelativeSubject;
use App\Models\Company;
use App\Models\CompanyCorrelativeFormat;
use App\Models\CompanyCorrelativeSequence;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class IssueCompanyCorrelativeCode
{
    /**
     * Vista previa del siguiente código (no incrementa la secuencia).
     */
    public function peek(Company $company, CorrelativeSubject $subject, string $series = '', ?int $year = null): string
    {
        $year = $year ?? (int) now()->year;
        $format = $this->resolveFormat($company, $subject, $series);

        $last = (int) CompanyCorrelativeSequence::query()
            ->where('company_id', $company->id)
            ->where('subject', $subject->value)
            ->where('series', $series)
            ->where('year', $year)
            ->value('last_number');

        return $this->compose($company, $format, $series, $year, $last + 1);
    }

    /**
     * Reserva e incrementa correlativo (transacción + bloqueo pesimista).
     */
    public function issue(Company $company, CorrelativeSubject $subject, string $series = '', ?int $year = null): string
    {
        $year = $year ?? (int) now()->year;

        return DB::transaction(function () use ($company, $subject, $series, $year): string {
            $format = $this->resolveFormat($company, $subject, $series);

            $sequence = CompanyCorrelativeSequence::query()
                ->where('company_id', $company->id)
                ->where('subject', $subject->value)
                ->where('series', $series)
                ->where('year', $year)
                ->lockForUpdate()
                ->first();

            if ($sequence === null) {
                CompanyCorrelativeSequence::query()->create([
                    'company_id' => $company->id,
                    'subject' => $subject->value,
                    'series' => $series,
                    'year' => $year,
                    'last_number' => 0,
                ]);

                $sequence = CompanyCorrelativeSequence::query()
                    ->where('company_id', $company->id)
                    ->where('subject', $subject->value)
                    ->where('series', $series)
                    ->where('year', $year)
                    ->lockForUpdate()
                    ->firstOrFail();
            }

            $sequence->last_number = $sequence->last_number + 1;
            $sequence->save();

            return $this->compose($company, $format, $series, $year, $sequence->last_number);
        });
    }

    protected function resolveFormat(Company $company, CorrelativeSubject $subject, string $series): CompanyCorrelativeFormat
    {
        $defaults = $subject->defaultFormat();

        return CompanyCorrelativeFormat::query()->firstOrCreate(
            [
                'company_id' => $company->id,
                'subject' => $subject->value,
                'series' => $series,
            ],
            [
                'suffix' => $defaults['suffix'],
                'template' => $defaults['template'],
                'pad_length' => $defaults['pad_length'],
                'is_active' => true,
            ],
        );
    }

    protected function resolvePrefix(Company $company): string
    {
        $manual = trim((string) ($company->correlative_prefix ?? ''));

        if ($manual !== '') {
            return mb_strtoupper($manual);
        }

        $ascii = (string) Str::ascii($company->name);
        $slug = preg_replace('/[^a-zA-Z0-9]+/', '', $ascii) ?? '';
        $slug = mb_substr($slug, 0, 12);

        return $slug !== '' ? mb_strtoupper($slug) : 'EMP';
    }

    protected function compose(Company $company, CompanyCorrelativeFormat $format, string $series, int $year, int $number): string
    {
        $prefix = $this->resolvePrefix($company);
        $padded = str_pad((string) $number, max(1, (int) $format->pad_length), '0', STR_PAD_LEFT);
        $seriesPart = trim($series);

        $out = str_replace(
            ['{prefix}', '{suffix}', '{year}', '{series}', '{number}'],
            [$prefix, $format->suffix, (string) $year, $seriesPart, $padded],
            $format->template,
        );

        $out = (string) preg_replace('/-{2,}/', '-', $out);

        return trim($out, '-');
    }
}
