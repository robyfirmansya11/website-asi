@php
    $pageLink = fn ($code) => isset($post) ? route('articles.show', [$code, $post]) : (($careerPage ?? false) ? ($vacancy ? route('careers.show', [$code, $vacancy]) : route('careers.index', $code)) : ($article !== null ? route('company.article', [$code, $article]) : route('company', $code)));
@endphp
<!DOCTYPE html>
<html lang="{{ $locale === 'zh' ? 'zh-Hans' : $locale }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ isset($post) ? $post->content($locale)['title'] : (($careerPage ?? false) ? ($vacancy ? $vacancy->content($locale)['title'] : __('careers.menu')) : ($article !== null ? $copy['news'][$article]['title'] : $copy['title'])) }} — PT Artabumi Sentra Industri</title>
    <meta name="description" content="{{ isset($post) ? $post->content($locale)['summary'] : ($article !== null ? $copy['news'][$article]['summary'] : $copy['description']) }}">
    <link rel="icon" href="{{ asset('company/logo.png') }}" type="image/png">
    <link rel="canonical" href="{{ $pageLink($locale) }}">
    @foreach(['id' => 'id', 'en' => 'en', 'zh' => 'zh-Hans'] as $code => $tag)
    <link rel="alternate" hreflang="{{ $tag }}" href="{{ $pageLink($code) }}">
    @endforeach
    <link rel="stylesheet" href="{{ asset('company/company.css') }}">
    <script src="{{ asset('company/company.js') }}" defer></script>
</head>
<body>
<a class="skip" href="#main">{{ $copy['skip'] }}</a>
<header class="header">
    <a class="brand" href="{{ route('company', $locale) }}"><img src="{{ asset('company/logo.png') }}" alt="" width="44" height="44"><span>ARTABUMI<small>SENTRA INDUSTRI</small></span></a>
    <button class="menu-toggle" aria-expanded="false" aria-controls="navigation" aria-label="{{ $copy['menu'] }}">☰</button>
    <nav id="navigation" aria-label="{{ $copy['menu'] }}">
        @foreach(['home','about','facilities','news','gallery'] as $i => $anchor)
        <a href="{{ route('company', $locale) }}#{{ $anchor }}">{{ $copy['nav'][$i] }}</a>
        @endforeach
        <a href="{{ route('careers.index', $locale) }}" @if($careerPage ?? false) aria-current="page" @endif>{{ __('careers.menu') }}</a>
    </nav>
    <div class="header-actions">
        <div class="languages" role="group" aria-label="{{ $copy['language'] }}">
        @foreach(['id' => 'ID', 'en' => 'EN', 'zh' => '中文'] as $code => $label)
        <a lang="{{ $code }}" href="{{ $pageLink($code) }}" @if($locale === $code) aria-current="true" @endif>{{ $label }}</a>
        @endforeach
        </div>
        <a class="button small" href="{{ route('company', $locale) }}#contact">{{ $copy['contact'] }} <span>↗</span></a>
    </div>
</header>
<main id="main">
@if($careerPage ?? false)
    @include('company.careers')
@elseif(isset($post))
    @include('news.show')
@elseif($article !== null)
    <article class="article section">
        <a class="text-link" href="{{ route('company', $locale) }}#news">← {{ $copy['back'] }}</a>
        <p class="eyebrow">{{ $copy['archive'] }} · {{ $copy['date'] }}</p>
        <h1>{{ $copy['news'][$article]['title'] }}</h1>
        <p class="lead">{{ $copy['news'][$article]['summary'] }}</p>
        <p>{{ $copy['news'][$article]['body'] }}</p>
        <aside>{{ $copy['archive_note'] }}</aside>
        <a class="text-link" href="https://www.artabumisentraindustri.com" target="_blank" rel="noopener noreferrer">{{ $copy['source'] }} ↗</a>
    </article>
@else
    <section id="home" class="hero">
        <img class="hero-image" src="{{ asset('company/lokasi-pertambangan.jpeg') }}" alt="{{ $copy['image_caption'] }}" fetchpriority="high">
        <div class="hero-shade"></div>
        <div class="hero-content">
            <p class="eyebrow"><span class="line"></span>{{ $copy['eyebrow'] }}</p>
            <h1>{{ $copy['hero'] }}<br><em>{{ $copy['hero_accent'] }}</em></h1>
            <p class="hero-intro">{{ $copy['intro'] }}</p>
            <div class="hero-links"><a class="button" href="#about">{{ $copy['discover'] }} <span>↗</span></a><a class="light-link" href="#facilities">{{ $copy['explore'] }} <span>↓</span></a></div>
        </div>
        <div class="hero-bottom"><span>PT ARTABUMI SENTRA INDUSTRI</span><span>↗ {{ $copy['location'] }}</span></div>
    </section>
    <div class="facts">@foreach($copy['facts'] as $value => $label)<div><strong>{{ $value }}</strong><span>{{ $label }}</span></div>@endforeach<span class="facts-mark">A / S / I</span></div>
    <section id="about" class="section about">
        <div><p class="eyebrow">{{ $copy['about_label'] }}</p><h2>{{ str_replace('\\n', "\n", $copy['about_title']) }}</h2><div class="about-photo"><img src="{{ asset('company/mining.jpeg') }}" alt="{{ $copy['photos'][0] }}" loading="lazy"><span>{{ $copy['location'] }}</span></div></div>
        <div class="about-copy"><p class="lead">{{ $copy['about'] }}</p><p>{{ $copy['about_more'] }}</p><div class="values">@foreach($copy['values'] as $i => $value)<div><span>{{ ['A','S','I'][$i] }}</span><div><h3>{{ $value[0] }}</h3><p>{{ $value[1] }}</p></div></div>@endforeach</div></div>
    </section>
    <section id="facilities" class="facilities section">
        <div class="section-heading"><div><p class="eyebrow">{{ $copy['facilities_label'] }}</p><h2>{{ str_replace('\\n', "\n", $copy['facilities_title']) }}</h2></div><p>{{ $copy['facilities_intro'] }}</p></div>
        <div class="facility-grid">@foreach(['smelter.jpg','furnace.jpg','mess.jpg'] as $i => $photo)<article><div class="facility-image"><img src="{{ asset('company/'.$photo) }}" alt="{{ $copy['facilities'][$i] }}" loading="lazy"><span>0{{ $i + 1 }}</span></div><h3>{{ $copy['facilities'][$i] }}</h3><p>{{ $copy['facility_text'][$i] }}</p></article>@endforeach</div>
    </section>
    @include('news.section')
    <section id="gallery" class="section gallery"><div class="section-heading"><div><p class="eyebrow">{{ $copy['gallery_label'] }}</p><h2>{{ $copy['gallery_title'] }}</h2><p>{{ $copy['gallery_intro'] }}</p></div><div class="filters" role="group" aria-label="{{ $copy['nav'][4] }}">@foreach(['all','site','facility'] as $i => $filter)<button data-filter="{{ $filter }}" aria-pressed="{{ $i === 0 ? 'true' : 'false' }}">{{ $copy['filters'][$i] }}</button>@endforeach</div></div>
        <div class="gallery-grid">@foreach(['lokasi-pertambangan.jpeg','mess.jpg','furnace.jpg','construction.jpg','smelter.jpg'] as $i => $photo)<button class="gallery-item" data-category="{{ in_array($i,[1,2]) ? 'facility' : 'site' }}" data-photo="{{ asset('company/'.$photo) }}" data-caption="{{ $copy['photos'][$i] }}" aria-label="{{ $copy['view_photo'] }}: {{ $copy['photos'][$i] }}"><img src="{{ asset('company/'.$photo) }}" alt="{{ $copy['photos'][$i] }}" loading="lazy"><span>{{ $copy['photos'][$i] }} <b>↗</b></span></button>@endforeach</div>
    </section>
@endif
    <section id="contact" class="section contact"><div><p class="eyebrow">{{ $copy['contact_label'] }}</p><h2>{{ str_replace('\\n', "\n", $copy['contact_title']) }}</h2><p>{{ $copy['contact_intro'] }}</p><a class="button" href="mailto:hello@artabumisentraindustri.com">{{ $copy['email'] }} <span>↗</span></a></div><div class="contact-details"><p class="eyebrow">{{ $copy['office'] }}</p><address>APL Tower, Lantai 35, Unit T1<br>Jl. Letjen S. Parman Kav. 28<br>Jakarta Barat 11470, Indonesia</address><a class="light-link" href="https://www.google.com/maps/search/?api=1&amp;query=APL+Tower+Jakarta+Barat" target="_blank" rel="noopener noreferrer">{{ $copy['map'] }} ↗</a><div class="contact-row"><span>{{ $copy['phone'] }}</span><a href="tel:+622122561925">+62 21 2256 1925</a></div><div class="contact-row"><span>Email</span><a href="mailto:hello@artabumisentraindustri.com">hello@artabumisentraindustri.com</a></div></div></section>
</main>
<footer><a class="brand" href="{{ route('company', $locale) }}"><img src="{{ asset('company/logo.png') }}" alt="" width="38" height="38"><span>ARTABUMI<small>SENTRA INDUSTRI</small></span></a><p>{{ $copy['footer'] }}</p><small>© {{ date('Y') }} PT Artabumi Sentra Industri. {{ $copy['rights'] }}</small></footer>
<dialog id="lightbox" aria-label="{{ $copy['nav'][4] }}"><button class="close-dialog" autofocus aria-label="{{ $copy['close'] }}">×</button><img src="" alt=""><p></p></dialog>
</body>
</html>
