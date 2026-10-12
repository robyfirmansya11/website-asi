<?php

namespace Database\Seeders;

use App\Models\Article;
use Illuminate\Database\Seeder;

class ArticleSeeder extends Seeder
{
    public function run(): void
    {
        if (Article::exists()) {
            return;
        }
        foreach (range(0, 2) as $index) {
            $translations = [];
            foreach (['id', 'en', 'zh'] as $locale) {
                $copy = require lang_path($locale.'/company.php');
                $translations[$locale] = $copy['news'][$index];
            }
            Article::create(['translations' => $translations, 'image_path' => 'company/'.['news-1.jpg', 'news-2.jpg', 'mining.jpeg'][$index],
                'status' => 'published', 'is_archive' => true, 'published_at' => '2025-04-15 00:00:00']);
        }
    }
}
