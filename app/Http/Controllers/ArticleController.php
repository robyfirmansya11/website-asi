<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Services\InstagramFeed;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ArticleController extends Controller
{
    public function show(string $locale, string $post)
    {
        app()->setLocale($locale);

        return view('welcome', ['locale' => $locale, 'copy' => trans('company'), 'article' => null,
            'post' => Article::published()->findOrFail($post)]);
    }

    public function index()
    {
        return view('news.index', ['articles' => Article::latest()->paginate(20)]);
    }

    public function create()
    {
        return view('news.form', ['post' => new Article]);
    }

    public function edit(Article $article)
    {
        return view('news.form', ['post' => $article]);
    }

    public function store(Request $request)
    {
        $article = new Article;
        $this->save($request, $article);

        return to_route('hr.articles.edit', $article)->with('status', 'Artikel disimpan. Tiga artikel terbit terbaru ditampilkan di beranda.');
    }

    public function update(Request $request, Article $article)
    {
        $this->save($request, $article);

        return to_route('hr.articles.edit', $article)->with('status', 'Perubahan artikel disimpan.');
    }

    public function sync(InstagramFeed $feed)
    {
        $feed->sync();

        return to_route('hr.articles.index')->with('status', Cache::get('instagram.status', 'Sinkronisasi sedang berlangsung.'));
    }

    private function save(Request $request, Article $article): void
    {
        $rules = ['translations' => ['required', 'array:id,en,zh'], 'status' => ['required', Rule::in(['draft', 'published'])],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120']];
        foreach (['id', 'en', 'zh'] as $locale) {
            $rules['translations.'.$locale] = [$locale === 'id' ? 'required' : 'nullable', 'array:title,summary,body'];
            $required = $locale === 'id' || collect($request->input('translations.'.$locale, []))->contains(fn ($value) => filled($value));
            foreach (['title' => 200, 'summary' => 500, 'body' => 50000] as $field => $max) {
                $rules['translations.'.$locale.'.'.$field] = [$required ? 'required' : 'nullable', 'string', 'max:'.$max];
            }
        }
        $data = $request->validate($rules);
        unset($data['image']);
        $newPath = null;
        if ($request->hasFile('image')) {
            $newPath = $request->file('image')->store('news', 'public');
            abort_unless($newPath, 500, 'Gambar gagal disimpan.');
            $data['image_path'] = 'storage/'.$newPath;
        }
        if ($data['status'] === 'published' && ! $article->published_at) {
            $data['published_at'] = now();
        }
        try {
            $article->fill($data)->save();
        } catch (\Throwable $error) {
            if ($newPath) {
                Storage::disk('public')->delete($newPath);
            }
            throw $error;
        }
    }
}
