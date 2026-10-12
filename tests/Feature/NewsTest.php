<?php

use App\Models\Article;
use App\Models\InstagramPost;
use App\Models\User;
use App\Services\InstagramFeed;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

function articlePayload(array $overrides = []): array
{
    return array_replace(['translations' => ['id' => ['title' => 'Berita perusahaan', 'summary' => 'Ringkasan berita.', 'body' => 'Isi artikel.']], 'status' => 'draft'], $overrides);
}

test('article management requires verified HR access', function () {
    $this->get('/hr/articles')->assertRedirect('/login');
    $this->actingAs(User::factory()->create())->post('/hr/articles', articlePayload())->assertForbidden();
    $this->post('/hr/instagram/sync')->assertForbidden();
    $this->actingAs(User::factory()->unverified()->create(['email' => 'recruitment@artabumisentraindustri.com']))->get('/hr/articles')->assertRedirect(route('verification.notice'));
});

test('HR can upload publish update and withdraw articles', function () {
    Storage::fake('public');
    $this->actingAs(User::factory()->create(['email' => 'recruitment@artabumisentraindustri.com']));
    $this->get('/hr/articles/create')->assertOk();
    $this->post('/hr/articles', articlePayload(['image' => UploadedFile::fake()->image('cover.jpg')]))->assertSessionHasNoErrors()->assertRedirect();
    $post = Article::firstOrFail();
    Storage::disk('public')->assertExists(str_replace('storage/', '', $post->image_path));
    $this->get('/id/articles/'.$post->id)->assertNotFound();
    $this->put('/hr/articles/'.$post->id, articlePayload(['status' => 'published']))->assertSessionHasNoErrors();
    $this->get('/id/articles/'.$post->id)->assertOk()->assertSee('Berita perusahaan');
    $this->get('/en/articles/'.$post->id)->assertOk()->assertSee('This article is available in Indonesian.')->assertSee('/zh/articles/'.$post->id, false);
    $this->get('/hr/articles')->assertOk()->assertSee('Berita perusahaan');
    $this->get('/hr/articles/'.$post->id.'/edit')->assertOk();
    $this->put('/hr/articles/'.$post->id, articlePayload())->assertSessionHasNoErrors();
    $this->get('/id/articles/'.$post->id)->assertNotFound();
});

test('homepage shows only three latest published backend articles', function () {
    foreach (range(1, 4) as $i) {
        $payload = articlePayload(['status' => 'published', 'published_at' => now()->subDays(5 - $i)]);
        $payload['translations']['id']['title'] = 'Artikel nomor '.$i;
        Article::create($payload);
    }
    Article::create(articlePayload());
    $this->get('/id')->assertOk()->assertDontSee('Artikel nomor 1')->assertSee('Artikel nomor 2')->assertSee('Artikel nomor 3')->assertSee('Artikel nomor 4')->assertDontSee('Berita perusahaan')->assertSee('Postingan Instagram akan tampil setelah akun terhubung.');
});

test('invalid uploads and partial translations are rejected', function () {
    $this->actingAs(User::factory()->create(['email' => 'recruitment@artabumisentraindustri.com']));
    $payload = articlePayload(['image' => UploadedFile::fake()->create('script.svg', 10, 'image/svg+xml')]);
    $payload['translations']['en'] = ['title' => 'Incomplete'];
    $this->post('/hr/articles', $payload)->assertSessionHasErrors(['image', 'translations.en.summary', 'translations.en.body']);
});

test('Instagram sync stores three newest posts and supports video thumbnails', function () {
    config(['instagram.access_token' => 'fake-test-token']);
    $items = collect(range(1, 4))->map(fn ($i) => ['id' => (string) $i, 'caption' => 'IG caption '.$i, 'permalink' => 'https://www.instagram.com/p/test'.$i.'/', 'media_type' => $i === 4 ? 'VIDEO' : 'IMAGE', 'media_url' => 'https://cdn.example.com/media.jpg', 'thumbnail_url' => 'https://cdn.example.com/video.jpg', 'timestamp' => '2026-09-0'.$i.'T12:00:00+0000'])->all();
    Http::fake(['*/me?*' => Http::response(['id' => '123', 'username' => 'artabumisentraindustri']), '*/me/media?*' => Http::response(['data' => $items])]);
    expect(app(InstagramFeed::class)->sync())->toBeTrue();
    expect(InstagramPost::count())->toBe(3);
    expect(InstagramPost::find('1'))->toBeNull();
    expect(InstagramPost::find('4')->image_url)->toBe('https://cdn.example.com/video.jpg');
    $this->get('/id')->assertSee('IG caption 4')->assertSee('https://www.instagram.com/p/test4/', false);
    expect(Cache::get('instagram.synced_at'))->not->toBeNull();
});

test('Instagram failures preserve last successful data without exposing token', function () {
    config(['instagram.access_token' => 'secret-test-token']);
    InstagramPost::create(['id' => '1', 'caption' => 'Saved post', 'permalink' => 'https://www.instagram.com/p/saved/', 'posted_at' => now()]);
    Http::fake(['*' => Http::response(['error' => 'secret-test-token'], 401)]);
    expect(app(InstagramFeed::class)->sync())->toBeFalse();
    expect(InstagramPost::count())->toBe(1);
    expect(Cache::get('instagram.status'))->not->toContain('secret-test-token');
});

test('Instagram missing token and account mismatch do not import posts', function () {
    config(['instagram.access_token' => null]);
    Http::fake();
    expect(app(InstagramFeed::class)->sync())->toBeFalse();
    Http::assertNothingSent();
    config(['instagram.access_token' => 'fake']);
    Http::fake(['*' => Http::response(['username' => 'another_account'])]);
    expect(app(InstagramFeed::class)->sync())->toBeFalse();
    expect(InstagramPost::count())->toBe(0);
});
