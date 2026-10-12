<!DOCTYPE html>
<html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="robots" content="noindex,nofollow"><title>{{ $title }} — HR Artabumi</title><link rel="icon" href="{{ asset('company/logo.png') }}"><link rel="stylesheet" href="{{ asset('company/company.css') }}"></head>
<body><header class="hr-header"><a class="brand" href="{{ route('hr.vacancies.index') }}"><img src="{{ asset('company/logo.png') }}" alt="" width="38" height="38"><span>ARTABUMI<small>HR / REKRUTMEN</small></span></a><nav><a href="{{ route('hr.vacancies.index') }}">Lowongan</a><a href="{{ route('hr.articles.index') }}">Berita &amp; Instagram</a><a href="{{ route('careers.index', 'id') }}" target="_blank" rel="noopener">Lihat halaman Karir ↗</a><a href="{{ route('dashboard') }}">Dashboard</a><form action="{{ route('logout') }}" method="post">@csrf<button type="submit">Keluar</button></form></nav></header>
<main class="hr-main">@if(session('status'))<p class="career-notice" role="status">{{ session('status') }}</p>@endif
@yield('content')
</main></body></html>
