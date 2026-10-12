<section class="section career-page">
    <p class="eyebrow">ARTABUMI / {{ __('careers.menu') }}</p>
    @if($vacancy)
        @php($content = $vacancy->content($locale))
        <a class="text-link" href="{{ route('careers.index', $locale) }}">← {{ __('careers.back') }}</a>
        <h1>{{ $content['title'] }}</h1>
        <p>{{ $vacancy->location }} · {{ __('careers.types.'.$vacancy->employment_type) }}</p>
        @if($locale !== 'id' && blank($vacancy->translations[$locale]['title'] ?? null))<p class="career-notice">{{ __('careers.fallback') }}</p>@endif
        <div class="career-detail">
            <div lang="{{ filled($vacancy->translations[$locale]['title'] ?? null) ? ($locale === 'zh' ? 'zh-Hans' : $locale) : 'id' }}">
                <h2>{{ __('careers.description') }}</h2><div class="job-text">{{ $content['description'] }}</div>
                <h2>{{ __('careers.requirements') }}</h2><div class="job-text">{{ $content['requirements'] }}</div>
            </div>
            <aside class="career-card">
                <h3>{{ __('careers.deadline') }}</h3><p>{{ $vacancy->closing_date?->format('d M Y') ?? __('careers.open') }} @if($vacancy->closing_date) (WIB) @endif</p>
                <p>{{ __('careers.apply_help') }}</p><p>{{ $vacancy->application_email }}</p>
                <a class="button" href="mailto:{{ $vacancy->application_email }}?subject={{ rawurlencode('Lamaran - '.$content['title']) }}">{{ __('careers.apply') }} ↗</a>
            </aside>
        </div>
    @else
        <h1>{{ __('careers.title') }}</h1><p class="lead">{{ __('careers.intro') }}</p>
        <div class="career-list">
        @forelse($vacancies as $position)
            @php($content = $position->content($locale))
            <article class="career-card"><div><p class="eyebrow">{{ __('careers.types.'.$position->employment_type) }}</p><h2>{{ $content['title'] }}</h2><p>{{ $position->location }} · {{ __('careers.deadline') }}: {{ $position->closing_date?->format('d M Y') ?? __('careers.open') }}</p></div><a class="button" href="{{ route('careers.show', [$locale, $position]) }}">{{ __('careers.detail') }} ↗</a></article>
        @empty
            <div class="career-card"><h2>{{ __('careers.empty') }}</h2><p>{{ __('careers.empty_help') }}</p></div>
        @endforelse
        </div>
        <nav class="career-pagination" aria-label="{{ __('careers.menu') }}">
            @if($vacancies->previousPageUrl())<a class="text-link" href="{{ $vacancies->previousPageUrl() }}">← {{ __('careers.previous') }}</a>@endif
            @if($vacancies->nextPageUrl())<a class="text-link" href="{{ $vacancies->nextPageUrl() }}">{{ __('careers.next') }} →</a>@endif
        </nav>
    @endif
</section>
