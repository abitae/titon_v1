<?php

namespace App\Models;

use App\Support\PublicStorageUrl;
use Database\Factories\ShowcaseProjectFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShowcaseProject extends Model
{
    /** @use HasFactory<ShowcaseProjectFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'slug',
        'summary',
        'description',
        'city',
        'client_name',
        'image_path',
        'is_published',
        'is_featured',
        'sort_order',
        'published_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'is_featured' => 'boolean',
            'sort_order' => 'integer',
            'published_at' => 'datetime',
        ];
    }

    public function imageUrl(): ?string
    {
        if (blank($this->image_path)) {
            return null;
        }

        return PublicStorageUrl::url($this->image_path);
    }

    /**
     * @param  Builder<ShowcaseProject>  $query
     * @return Builder<ShowcaseProject>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    /**
     * @param  Builder<ShowcaseProject>  $query
     * @return Builder<ShowcaseProject>
     */
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }
}
