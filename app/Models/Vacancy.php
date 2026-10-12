<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['translations', 'location', 'employment_type', 'application_email', 'status', 'closing_date'])]
class Vacancy extends Model
{
    protected function casts(): array
    {
        return ['translations' => 'array', 'closing_date' => 'date'];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')->where(function (Builder $query) {
            $query->whereNull('closing_date')->orWhereDate('closing_date', '>=', now('Asia/Jakarta')->toDateString());
        });
    }

    public function content(string $locale): array
    {
        return filled($this->translations[$locale]['title'] ?? null)
            ? $this->translations[$locale] : $this->translations['id'];
    }
}
