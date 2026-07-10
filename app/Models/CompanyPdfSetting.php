<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyPdfSetting extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'company_id',
        'show_logo',
        'header_layout',
        'show_company_name',
        'show_business_name',
        'show_ruc',
        'show_address',
        'show_phone',
        'show_email',
        'primary_color',
        'secondary_color',
        'footer_text',
        'margin_top',
        'margin_bottom',
        'margin_left',
        'margin_right',
        'show_page_numbers',
        'show_generated_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'show_logo' => 'boolean',
            'show_company_name' => 'boolean',
            'show_business_name' => 'boolean',
            'show_ruc' => 'boolean',
            'show_address' => 'boolean',
            'show_phone' => 'boolean',
            'show_email' => 'boolean',
            'show_page_numbers' => 'boolean',
            'show_generated_at' => 'boolean',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function defaultAttributes(): array
    {
        return [
            'show_logo' => true,
            'header_layout' => 'classic',
            'show_company_name' => true,
            'show_business_name' => true,
            'show_ruc' => true,
            'show_address' => true,
            'show_phone' => false,
            'show_email' => false,
            'primary_color' => null,
            'secondary_color' => null,
            'footer_text' => null,
            'margin_top' => 32,
            'margin_bottom' => 16,
            'margin_left' => 12,
            'margin_right' => 12,
            'show_page_numbers' => true,
            'show_generated_at' => true,
        ];
    }

    /**
     * @return BelongsTo<Company, $this>
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
