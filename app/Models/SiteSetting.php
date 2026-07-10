<?php

namespace App\Models;

use App\Support\PublicStorageUrl;
use Database\Factories\SiteSettingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

        return PublicStorageUrl::url($this->image_path);
    }

    public function faviconUrl(): ?string
    {
        if (blank($this->favicon_path)) {
            return null;
        }

        return PublicStorageUrl::url($this->favicon_path);
    }

    public function displayName(): string
    {
        return filled($this->title) ? $this->title : config('app.name', 'Titon');
    }
}
