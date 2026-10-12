@extends('hr.layout', ['title' => 'Kelola Lowongan'])
@section('content')
<div class="section-heading"><div><p class="eyebrow">HR / REKRUTMEN</p><h1>Kelola lowongan.</h1><p>Buat draf, terbitkan, atau tutup lowongan di website perusahaan.</p></div><a class="button" href="{{ route('hr.vacancies.create') }}">Tambah lowongan +</a></div>
@forelse($vacancies as $vacancy)
<article class="career-card hr-list"><div><p class="eyebrow">{{ ['draft'=>'Draf','published'=>'Terbit','closed'=>'Ditutup'][$vacancy->status] }} @if($vacancy->closing_date && $vacancy->closing_date->toDateString() < now('Asia/Jakarta')->toDateString()) · Kedaluwarsa @endif</p><h2>{{ $vacancy->content('id')['title'] }}</h2><p>{{ $vacancy->location }} · Batas: {{ $vacancy->closing_date?->format('d M Y') ?? 'Tanpa batas tanggal' }}</p></div><a class="text-link" href="{{ route('hr.vacancies.edit', $vacancy) }}">Edit lowongan →</a></article>
@empty
<div class="career-card"><h2>Belum ada lowongan.</h2><p>Mulai dengan membuat draf lowongan pertama Anda.</p></div>
@endforelse
<div class="career-pagination">@if($vacancies->previousPageUrl())<a class="text-link" href="{{ $vacancies->previousPageUrl() }}">← Sebelumnya</a>@endif @if($vacancies->nextPageUrl())<a class="text-link" href="{{ $vacancies->nextPageUrl() }}">Berikutnya →</a>@endif</div>
@endsection
