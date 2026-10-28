<article class="review-card">
    <div class="stars" aria-label="{{ $review->rating }} dari 5 bintang">@for($i = 1; $i <= 5; $i++)<span @class(['star-muted' => $i > $review->rating])>★</span>@endfor</div>
    <p class="review-quote">“{{ $review->review_text }}”</p>
    <div class="review-author">
        <span class="avatar">{{ mb_strtoupper(mb_substr($review->user_name, 0, 1)) }}</span>
        <div><strong>{{ $review->user_name }}</strong><small>{{ $review->created_at?->translatedFormat('d M Y') }}</small></div>
    </div>
</article>
