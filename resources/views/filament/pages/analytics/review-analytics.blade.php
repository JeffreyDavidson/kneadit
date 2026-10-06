<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Overall Statistics -->
        @php $stats = $this->getOverallStats(); @endphp
        <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
            <div class="rounded-lg bg-(--kn-info-tint) p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <x-heroicon-o-star class="h-8 w-8 text-(--kn-info)" stroke-width="2" />
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-(--kn-info)">Average Rating</p>
                        <p class="text-2xl font-bold text-(--kn-info)">{{ $stats['average_rating'] ?: '—' }}</p>
                        @if ($stats['total_reviews'] > 0)
                            <p class="text-xs text-(--kn-info)">from {{ $stats['total_reviews'] }} reviews</p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="rounded-lg bg-(--kn-success-tint) p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <x-heroicon-o-check-circle class="h-8 w-8 text-(--kn-success)" stroke-width="2" />
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-(--kn-success)">Total Reviews</p>
                        <p class="text-2xl font-bold text-(--kn-success)">{{ $stats['total_reviews'] }}</p>
                        <p class="text-xs text-(--kn-success)">{{ $stats['approved_reviews'] }} approved</p>
                    </div>
                </div>
            </div>

            <div class="rounded-lg bg-purple-50 p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <x-heroicon-o-arrow-trending-up class="h-8 w-8 text-purple-600" stroke-width="2" />
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-purple-600">Approval Rate</p>
                        <p class="text-2xl font-bold text-purple-900">{{ $stats['approval_rate'] }}%</p>
                    </div>
                </div>
            </div>

            @php $sentiment = $this->getSentimentAnalysis(); @endphp
            <div class="rounded-lg bg-(--kn-warning-tint) p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <x-heroicon-o-face-smile class="h-8 w-8 text-(--kn-warning)" stroke-width="2" />
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-(--kn-warning)">Positive Sentiment</p>
                        <p class="text-2xl font-bold text-(--kn-warning)">{{ $sentiment['positive'] }}%</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Rating Distribution -->
        @php $distribution = $this->getRatingDistribution(); @endphp
        <div class="rounded-lg bg-(--kn-surface) p-6 shadow">
            <h3 class="mb-4 text-lg font-semibold text-(--kn-ink)">Rating Distribution</h3>

            @if (array_sum(array_column($distribution, 'count')) > 0)
                <div class="space-y-3">
                    @foreach ($distribution as $rating)
                        <div class="flex items-center">
                            <div class="flex w-20 items-center">
                                <span class="mr-2 text-sm font-medium text-(--kn-ink-2)">{{ $rating['rating'] }} star{{ $rating['rating'] != 1 ? 's' : '' }}</span>
                            </div>
                            <div class="mx-4 flex-1">
                                <div class="h-4 rounded-full bg-(--kn-surface-hover)">
                                    <div
                                        class="h-4 rounded-full bg-yellow-400 transition-all duration-300"
                                        style="width: {{ $rating['percentage'] }}%"
                                    ></div>
                                </div>
                            </div>
                            <div class="w-16 text-right">
                                <span class="text-sm text-(--kn-muted)">{{ $rating['count'] }}</span>
                                <span class="text-xs text-(--kn-muted)">({{ $rating['percentage'] }}%)</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="py-8 text-center">
                    <x-heroicon-o-star class="mx-auto mb-4 h-12 w-12 text-(--kn-muted)" stroke-width="2" />
                    <p class="text-(--kn-muted)">No ratings data available</p>
                </div>
            @endif
        </div>

        <!-- Monthly Trend -->
        @php $trend = $this->getMonthlyTrend(); @endphp
        <div class="rounded-lg bg-(--kn-surface) p-6 shadow">
            <h3 class="mb-4 text-lg font-semibold text-(--kn-ink)">Monthly Review Trend</h3>

            @if (array_sum(array_column($trend, 'count')) > 0)
                <div class="grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6">
                    @foreach ($trend as $month)
                        @php
                            $maxCount = max(array_column($trend, 'count'));
                            $barHeight = $maxCount > 0 ? ($month['count'] / $maxCount) * 100 : 0;
                        @endphp
                        <div class="text-center">
                            <div class="mb-2 flex h-20 items-end justify-center">
                                <div
                                    class="w-8 rounded-t bg-blue-500 transition-all duration-300"
                                    style="height: {{ $barHeight }}%"
                                    title="{{ $month['count'] }} reviews"
                                ></div>
                            </div>
                            <div class="text-xs text-(--kn-muted)">{{ $month['month'] }}</div>
                            <div class="text-sm font-medium text-(--kn-ink)">{{ $month['count'] }}</div>
                            @if ($month['count'] > 0)
                                <div class="text-xs text-(--kn-warning)">{{ $month['avg_rating'] }}/5 avg</div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <div class="py-8 text-center">
                    <x-heroicon-o-chart-bar class="mx-auto mb-4 h-12 w-12 text-(--kn-muted)" stroke-width="2" />
                    <p class="text-(--kn-muted)">No trend data available</p>
                </div>
            @endif
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <!-- Top Reviewed Products -->
            <div class="rounded-lg bg-(--kn-surface) p-6 shadow">
                <h3 class="mb-4 text-lg font-semibold text-(--kn-ink)">Top Reviewed Products</h3>

                @php $topProducts = $this->getTopReviewedProducts(); @endphp
                @if ($topProducts->isNotEmpty())
                    <div class="space-y-3">
                        @foreach ($topProducts as $product)
                            <div class="flex items-center justify-between rounded-lg bg-(--kn-surface-sunken) p-3">
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-(--kn-ink)">{{ $product['name'] }}</p>
                                    <p class="text-xs text-(--kn-muted)">{{ $product['reviews_count'] }} reviews</p>
                                </div>
                                <div class="flex items-center">
                                    <span class="text-sm font-medium text-(--kn-ink)">{{ $product['average_rating'] }}/5</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="py-8 text-center">
                        <x-heroicon-o-tag class="mx-auto mb-4 h-12 w-12 text-(--kn-muted)" stroke-width="2" />
                        <p class="text-(--kn-muted)">No product reviews yet</p>
                    </div>
                @endif
            </div>

            <!-- Recent Reviews -->
            <div class="rounded-lg bg-(--kn-surface) p-6 shadow">
                <h3 class="mb-4 text-lg font-semibold text-(--kn-ink)">Recent Reviews</h3>

                @php $recentReviews = $this->getRecentReviews(); @endphp
                @if ($recentReviews->isNotEmpty())
                    <div class="space-y-4">
                        @foreach ($recentReviews as $review)
                            <div class="border-l-4 {{ $review['is_approved'] ? 'border-green-400 bg-(--kn-success-tint)' : 'border-yellow-400 bg-(--kn-warning-tint)' }} p-3 rounded-r-lg">
                                <div class="mb-2 flex items-center justify-between">
                                    <div class="flex items-center space-x-2">
                                        <span class="text-sm font-medium text-(--kn-ink)">{{ $review['customer_name'] }}</span>
                                        <div class="text-sm text-(--kn-warning)">{{ $review['rating'] }}/5</div>
                                    </div>
                                    <span class="text-xs text-(--kn-muted)">{{ $review['created_at']->diffForHumans() }}</span>
                                </div>
                                <p class="mb-1 text-sm text-(--kn-muted)">{{ $review['product_name'] }}</p>
                                @if ($review['comment'])
                                    <p class="text-sm text-(--kn-ink-2)">{{ Str::limit($review['comment'], 100) }}</p>
                                @endif
                                <div class="mt-2 flex items-center space-x-2">
                                    @if ($review['is_approved'])
                                        <span class="inline-flex items-center rounded bg-(--kn-success-tint) px-2 py-0.5 text-xs font-medium text-(--kn-success)">
                                            Approved
                                        </span>
                                    @else
                                        <span class="inline-flex items-center rounded bg-(--kn-warning-tint) px-2 py-0.5 text-xs font-medium text-(--kn-warning)">
                                            Pending
                                        </span>
                                    @endif

                                    @if ($review['is_featured'])
                                        <span class="inline-flex items-center rounded bg-purple-100 px-2 py-0.5 text-xs font-medium text-purple-800">
                                            Featured
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="py-8 text-center">
                        <x-heroicon-o-chat-bubble-oval-left
                            class="mx-auto mb-4 h-12 w-12 text-(--kn-muted)"
                            stroke-width="2"
                        />
                        <p class="text-(--kn-muted)">No reviews yet</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Sentiment Analysis -->
        <div class="rounded-lg bg-(--kn-surface) p-6 shadow">
            <h3 class="mb-4 text-lg font-semibold text-(--kn-ink)">Customer Sentiment</h3>

            @if ($sentiment['positive'] + $sentiment['neutral'] + $sentiment['negative'] > 0)
                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <div class="rounded-lg bg-(--kn-success-tint) p-4 text-center">
                        <div class="text-2xl font-bold text-(--kn-success)">{{ $sentiment['positive'] }}%</div>
                        <div class="text-sm text-(--kn-success)">Positive</div>
                        <div class="mt-2 h-2 w-full rounded-full bg-(--kn-surface-hover)">
                            <div
                                class="h-2 rounded-full bg-green-500 transition-all duration-300"
                                style="width: {{ $sentiment['positive'] }}%"
                            ></div>
                        </div>
                    </div>

                    <div class="rounded-lg bg-(--kn-info-tint) p-4 text-center">
                        <div class="text-2xl font-bold text-(--kn-info)">{{ $sentiment['neutral'] }}%</div>
                        <div class="text-sm text-(--kn-info)">Neutral</div>
                        <div class="mt-2 h-2 w-full rounded-full bg-(--kn-surface-hover)">
                            <div
                                class="h-2 rounded-full bg-blue-500 transition-all duration-300"
                                style="width: {{ $sentiment['neutral'] }}%"
                            ></div>
                        </div>
                    </div>

                    <div class="rounded-lg bg-(--kn-danger-tint) p-4 text-center">
                        <div class="text-2xl font-bold text-(--kn-danger)">{{ $sentiment['negative'] }}%</div>
                        <div class="text-sm text-(--kn-danger)">Negative</div>
                        <div class="mt-2 h-2 w-full rounded-full bg-(--kn-surface-hover)">
                            <div
                                class="h-2 rounded-full bg-red-500 transition-all duration-300"
                                style="width: {{ $sentiment['negative'] }}%"
                            ></div>
                        </div>
                    </div>
                </div>
            @else
                <div class="py-8 text-center">
                    <x-heroicon-o-face-smile class="mx-auto mb-4 h-12 w-12 text-(--kn-muted)" stroke-width="2" />
                    <p class="text-(--kn-muted)">No sentiment data available</p>
                </div>
            @endif
        </div>
    </div>
</x-filament-panels::page>
