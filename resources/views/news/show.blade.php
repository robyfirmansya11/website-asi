<article class="article section">
    @php($text = $post->content($locale))
    <a class="text-link" href="{{ route('company', $locale) }}#news">← {{ $copy['back'] }}</a>
    <p class="eyebrow">{{ $post->is_archive ? $copy['archive'] : __('news.editorial') }} · {{ $post->published_at->timezone('Asia/Jakarta')->format('d M Y') }}</p>
    <h1>{{ $text['title'] }}</h1><p class="lead">{{ $text['summary'] }}</p>
    @if($locale !== 'id' && blank($post->translations[$locale]['title'] ?? null))<p class="career-notice">{{ __('news.fallback') }}</p>@endif
    @if($post->image_path)<img class="article-cover" src="{{ asset($post->image_path) }}" alt="{{ $text['title'] }}">@endif
    <div class="job-text">{{ $text['body'] }}</div>
    @if($post->is_archive)<aside>{{ $copy['archive_note'] }}</aside>@endif
</article>
