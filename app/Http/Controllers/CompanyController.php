<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\InstagramPost;
use Illuminate\Contracts\View\View;

class CompanyController extends Controller
{
    public function index(string $locale = 'id'): View
    {
        app()->setLocale($locale);

        return view('welcome', ['locale' => $locale, 'copy' => trans('company'), 'article' => null,
            'articles' => Article::published()->orderByDesc('published_at')->orderByDesc('id')->limit(3)->get(),
            'instagramPosts' => InstagramPost::orderByDesc('posted_at')->limit(3)->get()]);
    }

    public function article(string $locale, string $article): View
    {
        app()->setLocale($locale);

        return view('welcome', ['locale' => $locale, 'copy' => trans('company'), 'article' => (int) $article]);
    }
}
