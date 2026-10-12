<?php

namespace App\Services;

use App\Models\InstagramPost;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class InstagramFeed
{
    public function sync(): bool
    {
        if (! config('instagram.access_token')) {
            Cache::forever('instagram.status', 'Token Instagram belum dikonfigurasi.');

            return false;
        }
        $lock = Cache::lock('instagram.sync', 60);
        if (! $lock->get()) {
            return false;
        }
        try {
            $base = 'https://graph.instagram.com/'.config('instagram.api_version');
            $client = Http::withToken(config('instagram.access_token'))->connectTimeout(5)->timeout(15);
            if (config('instagram.ca_bundle')) {
                $client = $client->withOptions(['verify' => config('instagram.ca_bundle')]);
            }
            $profile = $client->get($base.'/me', ['fields' => 'id,username'])->throw()->json();
            if (strtolower($profile['username'] ?? '') !== config('instagram.username')) {
                Cache::forever('instagram.status', 'Token tidak terhubung dengan akun Instagram perusahaan.');

                return false;
            }
            $response = $client->get($base.'/me/media', [
                'fields' => 'id,caption,media_type,media_url,thumbnail_url,permalink,timestamp', 'limit' => 25,
            ])->throw()->json();
            if (! is_array($response['data'] ?? null)) {
                throw new \RuntimeException('Invalid media response');
            }
            $posts = collect($response['data'])->sortByDesc('timestamp')->take(3)->map(function ($post) {
                $url = $post['permalink'] ?? '';
                if (parse_url($url, PHP_URL_SCHEME) !== 'https' || ! in_array(parse_url($url, PHP_URL_HOST), ['www.instagram.com', 'instagram.com'], true)) {
                    throw new \RuntimeException('Invalid permalink');
                }
                $image = ($post['media_type'] ?? '') === 'VIDEO' ? ($post['thumbnail_url'] ?? null) : ($post['media_url'] ?? null);
                if ($image && parse_url($image, PHP_URL_SCHEME) !== 'https') {
                    $image = null;
                }

                return ['id' => $post['id'], 'caption' => $post['caption'] ?? '', 'permalink' => $url,
                    'image_url' => $image, 'posted_at' => CarbonImmutable::parse($post['timestamp'])->utc()];
            });
            DB::transaction(function () use ($posts) {
                foreach ($posts as $post) {
                    InstagramPost::updateOrCreate(['id' => $post['id']], $post);
                }
                InstagramPost::whereNotIn('id', $posts->pluck('id'))->delete();
            });
            Cache::forever('instagram.status', 'Sinkronisasi berhasil.');
            Cache::forever('instagram.synced_at', now()->toIso8601String());

            return true;
        } catch (\Throwable) {
            // Do not log responses or exceptions: they can contain access tokens.
            Cache::forever('instagram.status', 'Sinkronisasi gagal. Periksa token, izin akun, dan koneksi. Data terakhir tetap ditampilkan.');

            return false;
        } finally {
            $lock->release();
        }
    }
}
