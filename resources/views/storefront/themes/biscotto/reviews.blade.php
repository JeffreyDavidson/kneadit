<section class="biscotto-reviews-hero">
    <p>From Our Customers</p>
    <h1>Kind Words</h1>
    @if ($vm->totalReviews > 0)
        <div class="biscotto-reviews-score" aria-label="{{ number_format($vm->avgRating, 1) }} out of 5 stars">
            <span aria-hidden="true">★★★★★</span>
            <strong>{{ number_format($vm->avgRating, 1) }}</strong>
            <small>from {{ $vm->totalReviews }} {{ Str::plural('review', $vm->totalReviews) }}</small>
        </div>
    @else
        <p class="biscotto-reviews-intro">Every order has a story. We can't wait to hear yours.</p>
    @endif
</section>

@if ($vm->featured())
    <section class="biscotto-reviews-featured">
        <div>
            <span aria-hidden="true">“</span>
            @if ($vm->featured()->comment)
                <blockquote>{{ $vm->featured()->comment }}</blockquote>
            @endif
            <div class="biscotto-review-stars" aria-label="{{ $vm->featured()->rating }} out of 5 stars">
                @for ($star = 1; $star <= 5; $star++)
                    <span class="{{ $star <= $vm->featured()->rating ? 'filled' : '' }}" aria-hidden="true">★</span>
                @endfor
            </div>
            <p>{{ $vm->featured()->customer_name }}</p>
            @if ($vm->featured()->product)
                <small>{{ $vm->featured()->product->name }}</small>
            @endif
        </div>
    </section>

    <section id="reviews" class="biscotto-reviews-list">
        <header>
            <span></span>
            <h2>More Love Notes</h2>
            <span></span>
        </header>
        <div class="biscotto-reviews-grid">
            @foreach ($vm->reviews->skip(1) as $review)
                <article>
                    <div class="biscotto-review-stars" aria-label="{{ $review->rating }} out of 5 stars">
                        @for ($star = 1; $star <= 5; $star++)
                            <span class="{{ $star <= $review->rating ? 'filled' : '' }}" aria-hidden="true">★</span>
                        @endfor
                    </div>
                    @if ($review->comment)
                        <blockquote>“{{ $review->comment }}”</blockquote>
                    @endif
                    <footer>
                        <strong>{{ $review->customer_name }}</strong>
                        @if ($review->product)
                            <small>{{ $review->product->name }}</small>
                        @endif
                    </footer>
                </article>
            @endforeach
        </div>
        <div class="biscotto-reviews-pagination">{{ $vm->reviews->fragment('reviews')->links() }}</div>
    </section>
@else
    <section class="biscotto-reviews-empty">
        <span aria-hidden="true">♡</span>
        <h2>No reviews yet</h2>
        <p>Be the first to share your experience.</p>
    </section>
@endif

<section class="biscotto-reviews-cta">
    <p>Enjoyed something?</p>
    <h2>We'd love to hear about it.</h2>
    <span>Your feedback helps our little bakery grow.</span>
    <a href="{{ route('order.track') }}">Leave a Review <span aria-hidden="true">→</span></a>
</section>
