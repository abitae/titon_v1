<?php

namespace App\Models;

use Database\Factories\SiteSettingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class SiteSetting extends Model
{
    /** @use HasFactory<SiteSettingFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'key',
        'title',
        'subtitle',
        'body',
        'cta_label',
        'cta_url',
        'image_path',
        'favicon_path',
        'is_active',
        'sort_order',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function imageUrl(): ?string
    {
        if (blank($this->image_path)) {
            return null;
        }

        return Storage::disk('public')->url($this->image_path);
    }

    public function faviconUrl(): ?string
    {
        if (blank($this->favicon_path)) {
            return null;
        }

        return Storage::disk('public')->url($this->favicon_path);
    }

    public function displayName(): string
    {
        return filled($this->title) ? $this->title : config('app.name', 'Titon');
    }
}
