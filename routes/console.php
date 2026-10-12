<?php

use App\Models\User;
use App\Services\InstagramFeed;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('careers:hr {email} {--revoke}', function () {
    if ($this->option('revoke') && in_array(strtolower($this->argument('email')), config('careers.hr_emails', []), true)) {
        $this->error('Hapus email dari config/careers.php terlebih dahulu untuk mencabut akses akun yang ditetapkan di konfigurasi.');

        return 1;
    }
    $user = User::where('email', $this->argument('email'))->first();
    if (! $user) {
        $this->error('Akun tidak ditemukan. Daftarkan akun terlebih dahulu.');

        return 1;
    }
    $user->is_hr = ! $this->option('revoke');
    $user->save();
    $this->info($user->is_hr ? 'Akses HR diberikan.' : 'Akses HR dicabut.');
})->purpose('Berikan atau cabut akses HR untuk akun yang sudah ada');

Artisan::command('instagram:sync', function (InstagramFeed $feed) {
    $ok = $feed->sync();
    $this->info(Cache::get('instagram.status', 'Sinkronisasi sedang berlangsung.'));

    return $ok ? 0 : 1;
})->purpose('Ambil tiga postingan terbaru akun Instagram perusahaan');

Schedule::command('instagram:sync')->everyThirtyMinutes()->withoutOverlapping();
