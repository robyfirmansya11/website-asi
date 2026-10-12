<section id="news" class="section news">
    <div class="section-heading"><div><p class="eyebrow">{{ $copy['news_label'] }}</p><h2>{{ $copy['news_title'] }}</h2></div><p>{{ __('news.intro') }}</p></div>
    <h3>{{ __('news.editorial') }}</h3>
    <div class="news-grid">
    @forelse($articles as $item)
        @php($text = $item->content($locale))
        <article>
            @if($item->image_path)<a class="news-image" href="{{ route('articles.show', [$locale, $item]) }}" tabindex="-1" aria-hidden="true"><img src="{{ asset($item->image_path) }}" alt="" loading="lazy"></a>@endif
            <p class="news-meta">{{ $item->is_archive ? $copy['archive'] : __('news.editorial') }} <span>{{ $item->published_at->timezone('Asia/Jakarta')->format('d M Y') }}</span></p>
            <h3><a href="{{ route('articles.show', [$locale, $item]) }}">{{ $text['title'] }}</a></h3><p>{{ $text['summary'] }}</p>
            <a class="text-link" href="{{ route('articles.show', [$locale, $item]) }}">{{ __('news.read') }} ↗</a>
        </article>
    @empty<p>{{ __('news.empty') }}</p>@endforelse
    </div>
    <div class="instagram-heading"><h3>{{ __('news.instagram') }}</h3><a class="text-link" href="https://www.instagram.com/artabumisentraindustri/" target="_blank" rel="noopener noreferrer">@artabumisentraindustri ↗</a></div>
    <div class="news-grid">
    @forelse($instagramPosts as $item)
        <article>
            @if($item->image_url)<a class="news-image" href="{{ $item->permalink }}" target="_blank" rel="noopener noreferrer" tabindex="-1" aria-hidden="true"><img src="{{ $item->image_url }}" alt="" loading="lazy" referrerpolicy="no-referrer"></a>@endif
            <p class="news-meta">INSTAGRAM <span>{{ $item->posted_at->timezone('Asia/Jakarta')->format('d M Y') }}</span></p>
            <h3>{{ \Illuminate\Support\Str::limit($item->caption ?: __('news.post'), 150) }}</h3>
            <a class="text-link" href="{{ $item->permalink }}" target="_blank" rel="noopener noreferrer">{{ __('news.view') }} ↗</a>
        </article>
    @empty
        <div class="instagram-empty"><p>{{ __('news.pending') }}</p><a class="text-link" href="https://www.instagram.com/artabumisentraindustri/" target="_blank" rel="noopener noreferrer">{{ __('news.profile') }} ↗</a></div>
    @endforelse
    </div>
</section>
