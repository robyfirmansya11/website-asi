@extends('hr.layout', ['title' => $post->exists ? 'Edit Artikel' : 'Tambah Artikel'])
@section('content')
<a class="text-link" href="{{ route('hr.articles.index') }}">← Berita & Instagram</a>
<h1>{{ $post->exists ? 'Edit artikel.' : 'Tambah artikel.' }}</h1>
<p>Isi bahasa Indonesia. Terjemahan Inggris dan Mandarin opsional; jika diisi, lengkapi seluruh kolom bahasa tersebut.</p>
@if($errors->any())<div class="form-errors" role="alert"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<form class="hr-form" method="post" enctype="multipart/form-data" action="{{ $post->exists ? route('hr.articles.update', $post) : route('hr.articles.store') }}">
@csrf @if($post->exists) @method('PUT') @endif
<label>Gambar sampul (JPG, PNG, WebP; maksimal 5 MB)<input type="file" name="image" accept="image/jpeg,image/png,image/webp"></label>
@if($post->image_path)<img class="admin-cover" src="{{ asset($post->image_path) }}" alt="Sampul saat ini"><p>Kosongkan unggahan untuk mempertahankan gambar ini.</p>@endif
@foreach(['id'=>'Indonesia (wajib)', 'en'=>'English (opsional)', 'zh'=>'中文 / Mandarin (opsional)'] as $code=>$label)
<fieldset><legend>{{ $label }}</legend>
<label>Judul<input name="translations[{{ $code }}][title]" maxlength="200" @required($code === 'id') value="{{ old('translations.'.$code.'.title', $post->translations[$code]['title'] ?? '') }}"></label>
<label>Ringkasan<textarea name="translations[{{ $code }}][summary]" rows="3" maxlength="500" @required($code === 'id')>{{ old('translations.'.$code.'.summary', $post->translations[$code]['summary'] ?? '') }}</textarea></label>
<label>Isi artikel<textarea name="translations[{{ $code }}][body]" rows="10" maxlength="50000" @required($code === 'id')>{{ old('translations.'.$code.'.body', $post->translations[$code]['body'] ?? '') }}</textarea></label>
</fieldset>
@endforeach
<label>Status<select name="status"><option value="draft" @selected(old('status', $post->status) !== 'published')>Draf — tidak tampil</option><option value="published" @selected(old('status', $post->status) === 'published')>Terbit — tampil di website</option></select></label>
<p>Tanggal publikasi ditetapkan saat pertama kali diterbitkan. Ubah menjadi Draf untuk menarik artikel dari website.</p>
<button class="button" type="submit">Simpan artikel ↗</button>
</form>
@endsection
