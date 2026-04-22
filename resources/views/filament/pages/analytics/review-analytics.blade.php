<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Overall Statistics -->
        @php $stats = $this->getOverallStats(); @endphp
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-blue-50 rounded-lg p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <x-heroicon-o-star class="w-8 h-8 text-blue-600" stroke-width="2" />
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-blue-600">Average Rating</p>
                        <p class="text-2xl font-bold text-blue-900">{{ $stats['average_rating'] ?: '—' }}</p>
                        @if ($stats['total_reviews'] > 0)
                            <p class="text-xs text-blue-600">from {{ $stats['total_reviews'] }} reviews</p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="bg-green-50 rounded-lg p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <x-heroicon-o-check-circle class="w-8 h-8 text-green-600" stroke-width="2" />
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-600">Total Reviews</p>
                        <p class="text-2xl font-bold text-green-900">{{ $stats['total_reviews'] }}</p>
                        <p class="text-xs text-green-600">{{ $stats['approved_reviews'] }} approved</p>
                    </div>
                </div>
            </div>

            <div class="bg-purple-50 rounded-lg p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <x-heroicon-o-arrow-trending-up class="w-8 h-8 text-purple-600" stroke-width="2" />
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-purple-600">Approval Rate</p>
                        <p class="text-2xl font-bold text-purple-900">{{ $stats['approval_rate'] }}%</p>
                    </div>
                </div>
            </div>

            @php $sentiment = $this->getSentimentAnalysis(); @endphp
            <div class="bg-orange-50 rounded-lg p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <x-heroicon-o-face-smile class="w-8 h-8 text-orange-600" stroke-width="2" />
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-orange-600">Positive Sentiment</p>
                        <p class="text-2xl font-bold text-orange-900">{{ $sentiment['positive'] }}%</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Rating Distribution -->
        @php $distribution = $this->getRatingDistribution(); @endphp
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Rating Distribution</h3>

            @if (array_sum(array_column($distribution, 'count')) > 0)
                <div class="space-y-3">
                    @foreach ($distribution as $rating)
                        <div class="flex items-center">
                            <div class="flex items-center w-20">
                                <span class="text-sm font-medium text-gray-700 mr-2">{{ $rating['rating'] }} star{{ $rating['rating'] != 1 ? 's' : '' }}</span>
                            </div>
                            <div class="flex-1 mx-4">
                                <div class="bg-gray-200 rounded-full h-4">
                                    <div class="bg-yellow-400 h-4 rounded-full transition-all duration-300"
                                         style="width: {{ $rating['percentage'] }}%"></div>
                                </div>
                            </div>
                            <div class="w-16 text-right">
                                <span class="text-sm text-gray-600">{{ $rating['count'] }}</span>
                                <span class="text-xs text-gray-400">({{ $rating['percentage'] }}%)</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <x-heroicon-o-star class="w-12 h-12 mx-auto text-gray-400 mb-4" stroke-width="2" />
                    <p class="text-gray-500">No ratings data available</p>
                </div>
            @endif
        </div>

        <!-- Monthly Trend -->
        @php $trend = $this->getMonthlyTrend(); @endphp
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Monthly Review Trend</h3>

            @if (array_sum(array_column($trend, 'count')) > 0)
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-4">
                    @foreach ($trend as $month)
                        @php
                            $maxCount = max(array_column($trend, 'count'));
                            $barHeight = $maxCount > 0 ? ($month['count'] / $maxCount) * 100 : 0;
                        @endphp
                        <div class="text-center">
                            <div class="flex items-end justify-center h-20 mb-2">
                                <div class="bg-blue-500 rounded-t w-8 transition-all duration-300"
                                     style="height: {{ $barHeight }}%"
                                     title="{{ $month['count'] }} reviews"></div>
                            </div>
                            <div class="text-xs text-gray-600">{{ $month['month'] }}</div>
                            <div class="text-sm font-medium text-gray-900">{{ $month['count'] }}</div>
                            @if ($month['count'] > 0)
                                <div class="text-xs text-yellow-600">★ {{ $month['avg_rating'] }}</div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <x-heroicon-o-chart-bar class="w-12 h-12 mx-auto text-gray-400 mb-4" stroke-width="2" />
                    <p class="text-gray-500">No trend data available</p>
                </div>
            @endif
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Top Reviewed Products -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Top Reviewed Products</h3>

                @php $topProducts = $this->getTopReviewedProducts(); @endphp
                @if ($topProducts->isNotEmpty())
                    <div class="space-y-3">
                        @foreach ($topProducts as $product)
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-900">{{ $product['name'] }}</p>
                                    <p class="text-xs text-gray-600">{{ $product['reviews_count'] }} reviews</p>
                                </div>
                                <div class="flex items-center">
                                    <span class="text-yellow-400 mr-1">★</span>
                                    <span class="text-sm font-medium text-gray-900">{{ $product['average_rating'] }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <x-heroicon-o-tag class="w-12 h-12 mx-auto text-gray-400 mb-4" stroke-width="2" />
                        <p class="text-gray-500">No product reviews yet</p>
                    </div>
                @endif
            </div>

            <!-- Recent Reviews -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Reviews</h3>

                @php $recentReviews = $this->getRecentReviews(); @endphp
                @if ($recentReviews->isNotEmpty())
                    <div class="space-y-4">
                        @foreach ($recentReviews as $review)
                            <div class="border-l-4 {{ $review['is_approved'] ? 'border-green-400 bg-green-50' : 'border-yellow-400 bg-yellow-50' }} p-3 rounded-r-lg">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center space-x-2">
                                        <span class="text-sm font-medium text-gray-900">{{ $review['customer_name'] }}</span>
                                        <div class="flex items-center">
                                            @for ($i = 1; $i <= 5; $i++)
                                                <span class="{{ $i <= $review['rating'] ? 'text-yellow-400' : 'text-gray-300' }}">★</span>
                                            @endfor
                                        </div>
                                    </div>
                                    <span class="text-xs text-gray-500">{{ $review['created_at']->diffForHumans() }}</span>
                                </div>
                                <p class="text-sm text-gray-600 mb-1">{{ $review['product_name'] }}</p>
                                @if ($review['comment'])
                                    <p class="text-sm text-gray-800">{{ Str::limit($review['comment'], 100) }}</p>
                                @endif
                                <div class="flex items-center space-x-2 mt-2">
                                    @if ($review['is_approved'])
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                            Approved
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">
                                            Pending
                                        </span>
                                    @endif

                                    @if ($review['is_featured'])
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800">
                                            Featured
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <x-heroicon-o-chat-bubble-oval-left class="w-12 h-12 mx-auto text-gray-400 mb-4" stroke-width="2" />
                        <p class="text-gray-500">No reviews yet</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Sentiment Analysis -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Customer Sentiment</h3>

            @if ($sentiment['positive'] + $sentiment['neutral'] + $sentiment['negative'] > 0)
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="text-center p-4 bg-green-50 rounded-lg">
                        <div class="text-2xl font-bold text-green-600">{{ $sentiment['positive'] }}%</div>
                        <div class="text-sm text-green-700">Positive</div>
                        <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                            <div class="bg-green-500 h-2 rounded-full transition-all duration-300" style="width: {{ $sentiment['positive'] }}%"></div>
                        </div>
                    </div>

                    <div class="text-center p-4 bg-blue-50 rounded-lg">
                        <div class="text-2xl font-bold text-blue-600">{{ $sentiment['neutral'] }}%</div>
                        <div class="text-sm text-blue-700">Neutral</div>
                        <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                            <div class="bg-blue-500 h-2 rounded-full transition-all duration-300" style="width: {{ $sentiment['neutral'] }}%"></div>
                        </div>
                    </div>

                    <div class="text-center p-4 bg-red-50 rounded-lg">
                        <div class="text-2xl font-bold text-red-600">{{ $sentiment['negative'] }}%</div>
                        <div class="text-sm text-red-700">Negative</div>
                        <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                            <div class="bg-red-500 h-2 rounded-full transition-all duration-300" style="width: {{ $sentiment['negative'] }}%"></div>
                        </div>
                    </div>
                </div>
            @else
                <div class="text-center py-8">
                    <x-heroicon-o-face-smile class="w-12 h-12 mx-auto text-gray-400 mb-4" stroke-width="2" />
                    <p class="text-gray-500">No sentiment data available</p>
                </div>
            @endif
        </div>
    </div>
</x-filament-panels::page>