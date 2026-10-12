@extends('hr.layout', ['title' => $vacancy->exists ? 'Edit Lowongan' : 'Tambah Lowongan'])
@section('content')
<a class="text-link" href="{{ route('hr.vacancies.index') }}">← Semua lowongan</a>
<h1>{{ $vacancy->exists ? 'Edit lowongan.' : 'Tambah lowongan.' }}</h1>
<p>Bahasa Indonesia wajib diisi. Jika terjemahan belum tersedia, pengunjung akan melihat isi bahasa Indonesia.</p>
@if($errors->any())<div class="form-errors" role="alert"><strong>Periksa kembali data lowongan:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<form class="hr-form" method="post" action="{{ $vacancy->exists ? route('hr.vacancies.update', $vacancy) : route('hr.vacancies.store') }}">
@csrf @if($vacancy->exists) @method('PUT') @endif
<div class="hr-fields">
<label>Lokasi kerja <input required maxlength="200" name="location" value="{{ old('location', $vacancy->location) }}" placeholder="Contoh: Morowali, Sulawesi Tengah"></label>
<label>Jenis pekerjaan <select name="employment_type" required>@foreach(['full_time'=>'Penuh waktu','contract'=>'Kontrak','internship'=>'Magang'] as $key=>$label)<option value="{{ $key }}" @selected(old('employment_type', $vacancy->employment_type) === $key)>{{ $label }}</option>@endforeach</select></label>
<label>Email penerima lamaran <input type="email" name="application_email" required maxlength="254" value="{{ old('application_email', $vacancy->application_email ?? 'recruitment@artabumisentraindustri.com') }}"></label>
<label>Batas lamaran (WIB, opsional) <input type="date" name="closing_date" value="{{ old('closing_date', $vacancy->closing_date?->format('Y-m-d')) }}"></label>
</div>
@foreach(['id'=>'Indonesia (wajib)', 'en'=>'English (opsional)', 'zh'=>'中文 / Mandarin (opsional)'] as $code=>$language)
<fieldset><legend>{{ $language }}</legend><p>Jika mengisi terjemahan, lengkapi ketiga kolom bahasa tersebut.</p>
<label>Judul posisi <input name="translations[{{ $code }}][title]" maxlength="200" @required($code === 'id') value="{{ old('translations.'.$code.'.title', $vacancy->translations[$code]['title'] ?? '') }}"></label>
<label>Deskripsi pekerjaan <textarea name="translations[{{ $code }}][description]" rows="6" maxlength="20000" @required($code === 'id')>{{ old('translations.'.$code.'.description', $vacancy->translations[$code]['description'] ?? '') }}</textarea></label>
<label>Persyaratan <textarea name="translations[{{ $code }}][requirements]" rows="6" maxlength="20000" @required($code === 'id')>{{ old('translations.'.$code.'.requirements', $vacancy->translations[$code]['requirements'] ?? '') }}</textarea></label>
</fieldset>
@endforeach
<div class="career-card"><label>Status publikasi <select name="status" required>@foreach(['draft'=>'Draf — belum tampil di website','published'=>'Terbit — tampil di website','closed'=>'Ditutup — disembunyikan dari website'] as $key=>$label)<option value="{{ $key }}" @selected(old('status', $vacancy->status ?? 'draft') === $key)>{{ $label }}</option>@endforeach</select></label><p>Lowongan yang melewati batas lamaran otomatis tidak tampil. Pilih Terbit dan simpan untuk mempublikasikan lowongan.</p><button class="button" type="submit">Simpan lowongan ↗</button></div>
</form>
@endsection
