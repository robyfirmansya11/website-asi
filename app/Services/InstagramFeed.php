<?php

namespace App\Services;

use App\Models\InstagramPost;
use Carbon\CarbonImmutable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
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
        $caBundle = config('instagram.ca_bundle');
        if ($caBundle && (! is_string($caBundle) || ! is_file($caBundle) || ! is_readable($caBundle))) {
            Cache::forever('instagram.status', 'Konfigurasi sertifikat SSL tidak valid. Periksa INSTAGRAM_CA_BUNDLE di server hosting. Data terakhir tetap ditampilkan.');

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
        } catch (RequestException $exception) {
            $code = $exception->response->json('error.code');
            $status = $exception->response->status();
            $reason = match (true) {
                $code === 190 || $status === 401 => 'Token Instagram ditolak atau tidak berlaku. Hubungkan ulang akun dan perbarui token di hosting.',
                in_array($code, [10, 200], true) || $status === 403 => 'Akses Instagram ditolak. Periksa izin aplikasi dan akses akun perusahaan.',
                in_array($code, [4, 17, 32, 613], true) || $status === 429 => 'Batas permintaan Instagram tercapai. Tunggu sebelum mencoba lagi.',
                $status >= 500 => 'Layanan Instagram sedang bermasalah. Coba kembali nanti.',
                default => 'Permintaan ke API Instagram gagal (HTTP '.$status.'). Periksa konfigurasi API dan akses aplikasi.',
            };
            Cache::forever('instagram.status', $reason.' Data terakhir tetap ditampilkan.');

            return false;
        } catch (ConnectionException) {
            Cache::forever('instagram.status', 'Tidak dapat terhubung ke Instagram. Periksa koneksi keluar hosting, DNS, sertifikat SSL, atau timeout. Data terakhir tetap ditampilkan.');

            return false;
        } catch (\Throwable) {
            // Do not log responses or exceptions: they can contain access tokens.
            Cache::forever('instagram.status', 'Sinkronisasi gagal. Periksa token, izin akun, dan koneksi. Data terakhir tetap ditampilkan.');

            return false;
        } finally {
            $lock->release();
        }
    }
}
