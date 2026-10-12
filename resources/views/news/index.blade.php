@extends('hr.layout', ['title' => 'Berita & Instagram'])
@section('content')
<div class="section-heading"><div><p class="eyebrow">PUBLIKASI WEBSITE</p><h1>Berita & Instagram.</h1><p>Beranda menampilkan tiga artikel terbit terbaru dan tiga postingan Instagram terakhir yang berhasil disinkronkan.</p></div><a class="button" href="{{ route('hr.articles.create') }}">Tambah artikel +</a></div>
<section class="career-card"><h2>Instagram · @artabumisentraindustri</h2>
<p>{{ config('instagram.access_token') ? 'Token sudah diisi. Gunakan sinkronisasi untuk memeriksa koneksi.' : 'Belum terhubung. Siapkan akses API akun Business/Creator, lalu isi INSTAGRAM_ACCESS_TOKEN di konfigurasi server.' }}</p>
<p role="status">{{ \Illuminate\Support\Facades\Cache::get('instagram.status', 'Belum pernah disinkronkan.') }}</p>
@if($synced = \Illuminate\Support\Facades\Cache::get('instagram.synced_at'))<p>Sinkronisasi berhasil terakhir: {{ \Carbon\CarbonImmutable::parse($synced)->timezone('Asia/Jakarta')->format('d M Y H:i') }} WIB</p>@endif
<p>Sinkronisasi otomatis setiap 30 menit setelah scheduler server diaktifkan. Caption Instagram mengikuti bahasa postingan asli.</p>
<form method="post" action="{{ route('hr.instagram.sync') }}">@csrf<button class="button" type="submit" @disabled(!config('instagram.access_token'))>Sinkronkan sekarang ↻</button></form>
</section>
@forelse($articles as $post)
<article class="career-card hr-list"><div><p class="eyebrow">{{ $post->status === 'published' ? 'Terbit' : 'Draf' }} {{ $post->is_archive ? '· Arsip' : '' }}</p><h2>{{ $post->content('id')['title'] }}</h2><p>{{ $post->published_at?->timezone('Asia/Jakarta')->format('d M Y H:i') }}</p></div><a class="text-link" href="{{ route('hr.articles.edit', $post) }}">Edit artikel →</a></article>
@empty<p>Belum ada artikel. Tambahkan artikel pertama Anda.</p>@endforelse
<div class="career-pagination">@if($articles->previousPageUrl())<a href="{{ $articles->previousPageUrl() }}">← Sebelumnya</a>@endif @if($articles->nextPageUrl())<a href="{{ $articles->nextPageUrl() }}">Berikutnya →</a>@endif</div>
@endsection
