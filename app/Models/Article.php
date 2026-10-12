<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['translations', 'image_path', 'status', 'published_at', 'is_archive'])]
class Article extends Model
{
    protected function casts(): array
    {
        return ['translations' => 'array', 'published_at' => 'datetime', 'is_archive' => 'boolean'];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')->whereNotNull('published_at')->where('published_at', '<=', now());
    }

    public function content(string $locale): array
    {
        return filled($this->translations[$locale]['title'] ?? null) ? $this->translations[$locale] : $this->translations['id'];
    }
}
