<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('マイ読書レポート') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- 基本統計 -->
            <div class="bg-white overflow-hidden border border-gray-200 rounded-lg mb-6">
                <div class="p-6">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
                        <!-- 総読了冊数 -->
                        <div class="group cursor-help">
                            <div class="text-4xl font-bold text-gray-900 mb-3">{{ $stats['total_books_read'] }}</div>
                            <div class="flex items-center gap-2">
                                <span class="text-lg">📚</span>
                                <div class="text-sm text-gray-600">読んだ本の冊数</div>
                            </div>
                            <!-- ホバー時の説明 -->
                            <div
                                class="invisible group-hover:visible absolute bg-gray-900 text-white text-xs rounded px-3 py-2 mt-2 whitespace-nowrap z-10 shadow-lg">
                                レビューを投稿した<br>ユニークな書籍の数
                            </div>
                        </div>

                        <!-- レビュー投稿日数 -->
                        <div class="group cursor-help">
                            <div class="text-4xl font-bold text-gray-900 mb-3">{{ $stats['review_posting_days'] }}</div>
                            <div class="flex items-center gap-2">
                                <span class="text-lg">✍️</span>
                                <div class="text-sm text-gray-600">レビューを書いた日数</div>
                            </div>
                            <!-- ホバー時の説明 -->
                            <div
                                class="invisible group-hover:visible absolute bg-gray-900 text-white text-xs rounded px-3 py-2 mt-2 whitespace-nowrap z-10 shadow-lg">
                                レビューを投稿した<br>ユニークな日付の数
                            </div>
                        </div>

                        <!-- 現在の読書継続日数 -->
                        <div class="group cursor-help">
                            <div class="text-4xl font-bold text-gray-900 mb-3">{{ $stats['streaks']['current'] }}</div>
                            <div class="flex items-center gap-2">
                                <span class="text-lg">🔥</span>
                                <div class="text-sm text-gray-600">現在の連続日数</div>
                            </div>
                            <!-- ホバー時の説明 -->
                            <div
                                class="invisible group-hover:visible absolute bg-gray-900 text-white text-xs rounded px-3 py-2 mt-2 whitespace-nowrap z-10 shadow-lg">
                                今日または昨日から<br>遡った連続投稿日数
                            </div>
                        </div>

                        <!-- 最長の読書継続日数 -->
                        <div class="group cursor-help">
                            <div class="text-4xl font-bold text-gray-900 mb-3">{{ $stats['streaks']['longest'] }}</div>
                            <div class="flex items-center gap-2">
                                <span class="text-lg">⭐</span>
                                <div class="text-sm text-gray-600">過去最長の連続日数</div>
                            </div>
                            <!-- ホバー時の説明 -->
                            <div
                                class="invisible group-hover:visible absolute bg-gray-900 text-white text-xs rounded px-3 py-2 mt-2 whitespace-nowrap z-10 shadow-lg">
                                これまでの最長の<br>連続投稿記録
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 月別統計 -->
            <div class="bg-white overflow-hidden border border-gray-200 rounded-lg">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-sm font-semibold text-gray-900">📈 月別統計</h3>
                        <div class="text-xs text-gray-500">過去12ヶ月の推移</div>
                    </div>
                    <div style="height: 320px; position: relative;">
                        <canvas id="monthlyStatsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const monthlyStatsData = @json($stats['monthly_stats']);
                const labels = monthlyStatsData.map(data => data.month);
                const booksReadData = monthlyStatsData.map(data => data.books_read);
                const reviewsPostedData = monthlyStatsData.map(data => data.reviews_posted);

                const ctx = document.getElementById('monthlyStatsChart').getContext('2d');

                let chart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                                label: '読了冊数',
                                data: booksReadData,
                                backgroundColor: 'rgba(59, 130, 246, 0.7)',
                                borderColor: 'rgba(59, 130, 246, 1)',
                                borderWidth: 0,
                                borderRadius: 4,
                                barPercentage: 0.7,
                                categoryPercentage: 0.8,
                            },
                            {
                                label: 'レビュー投稿数',
                                data: reviewsPostedData,
                                backgroundColor: 'rgba(34, 197, 94, 0.7)',
                                borderColor: 'rgba(34, 197, 94, 1)',
                                borderWidth: 0,
                                borderRadius: 4,
                                barPercentage: 0.7,
                                categoryPercentage: 0.8,
                            }
                        ]
                    },
                    options: {
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0,
                                    font: {
                                        size: 11
                                    }
                                },
                                grid: {
                                    drawBorder: false,
                                    color: 'rgba(0, 0, 0, 0.05)'
                                }
                            },
                            x: {
                                grid: {
                                    display: false,
                                    drawBorder: false
                                },
                                ticks: {
                                    font: {
                                        size: 10
                                    }
                                }
                            }
                        },
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'bottom',
                                labels: {
                                    boxWidth: 12,
                                    padding: 15,
                                    font: {
                                        size: 12
                                    },
                                    usePointStyle: true,
                                    pointStyle: 'rect'
                                }
                            },
                            title: {
                                display: false
                            }
                        }
                    }
                });
            });
        </script>
    @endpush
</x-app-layout>




{{-- resources/views/reports/index.blade.php --}}
{{--
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('マイ読書レポート') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- 基本統計 -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold mb-4">📊 基本統計</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <!-- 総読了冊数 -->
                        <div class="bg-blue-50 p-4 rounded-lg text-center">
                            <div class="text-3xl font-bold text-blue-600">{{ $stats['total_books_read'] }}</div>
                            <div class="text-sm text-gray-600 mt-1">総読了冊数</div>
                        </div>

                        <!-- レビュー投稿日数 -->
                        <div class="bg-green-50 p-4 rounded-lg text-center">
                            <div class="text-3xl font-bold text-green-600">{{ $stats['review_posting_days'] }}</div>
                            <div class="text-sm text-gray-600 mt-1">レビュー投稿日数</div>
                        </div>

                        <!-- 現在の読書継続日数 -->
                        <div class="bg-purple-50 p-4 rounded-lg text-center">
                            <div class="text-3xl font-bold text-purple-600">{{ $stats['streaks']['current'] }}</div>
                            <div class="text-sm text-gray-600 mt-1">現在の読書継続日数</div>
                        </div>

                        <!-- 最長の読書継続日数 -->
                        <div class="bg-pink-50 p-4 rounded-lg text-center">
                            <div class="text-3xl font-bold text-pink-600">{{ $stats['streaks']['longest'] }}</div>
                            <div class="text-sm text-gray-600 mt-1">最長の読書継続日数</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 月別統計 -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold mb-4">📈 月別統計（過去12ヶ月）</h3>
                    <canvas id="monthlyStatsChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const monthlyStatsData = @json($stats['monthly_stats']);

                const labels = monthlyStatsData.map(data => data.month);
                const booksReadData = monthlyStatsData.map(data => data.books_read);
                const reviewsPostedData = monthlyStatsData.map(data => data.reviews_posted);

                const ctx = document.getElementById('monthlyStatsChart').getContext('2d');
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                                label: '月別読了冊数',
                                data: booksReadData,
                                backgroundColor: 'rgba(59, 130, 246, 0.5)',
                                borderColor: 'rgba(59, 130, 246, 1)',
                                borderWidth: 1
                            },
                            {
                                label: '月別レビュー投稿数',
                                data: reviewsPostedData,
                                backgroundColor: 'rgba(16, 185, 129, 0.5)',
                                borderColor: 'rgba(16, 185, 129, 1)',
                                borderWidth: 1
                            }
                        ]
                    },
                    options: {
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    // Y軸の目盛りを整数のみに設定
                                    precision: 0
                                }
                            }
                        },
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'top',
                            },
                            title: {
                                display: true,
                                text: '月別読了冊数とレビュー投稿数の推移'
                            }
                        }
                    }
                });
            });
        </script>
    @endpush
</x-app-layout>
--}}

{{-- resources/views/reports/index.blade.php --}}
{{--
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('マイ読書レポート') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- 基本統計サマリー -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">📊 基本統計</h3>
                    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                        <div class="bg-blue-50 rounded-lg p-4 text-center">
                            <div class="text-3xl font-bold text-blue-600">{{ $stats['summary']['total_reviews'] }}</div>
                            <div class="text-sm text-gray-600">総レビュー数</div>
                        </div>
                        <div class="bg-green-50 rounded-lg p-4 text-center">
                            <div class="text-3xl font-bold text-green-600">
                                {{ $stats['summary']['total_books_reviewed'] }}</div>
                            <div class="text-sm text-gray-600">レビュー済み書籍</div>
                        </div>
                        <div class="bg-yellow-50 rounded-lg p-4 text-center">
                            <div class="text-3xl font-bold text-yellow-600">
                                {{ number_format($stats['summary']['average_rating'], 1) }}</div>
                            <div class="text-sm text-gray-600">平均評価</div>
                        </div>
                        <div class="bg-purple-50 rounded-lg p-4 text-center">
                            <div class="text-3xl font-bold text-purple-600">
                                {{ $stats['reading_streak']['current_streak'] }}</div>
                            <div class="text-sm text-gray-600">現在の連続日数</div>
                        </div>
                        <div class="bg-pink-50 rounded-lg p-4 text-center">
                            <div class="text-3xl font-bold text-pink-600">
                                {{ $stats['reading_streak']['longest_streak'] }}</div>
                            <div class="text-sm text-gray-600">最長連続日数</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- 評価分布 -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4">⭐ 評価分布</h3>
                        <div class="space-y-3">
                            @foreach ($stats['rating_distribution'] as $rating => $count)
                                @php
                                    $maxCount = max($stats['rating_distribution']->toArray()) ?: 1;
                                    $percentage = ($count / $maxCount) * 100;
                                @endphp
                                <div class="flex items-center">
                                    <div class="w-12 text-sm font-medium">{{ $rating }}星</div>
                                    <div class="flex-1 mx-2">
                                        <div class="bg-gray-200 rounded-full h-4">
                                            <div class="bg-yellow-400 h-4 rounded-full"
                                                style="width: {{ $percentage }}%"></div>
                                        </div>
                                    </div>
                                    <div class="w-8 text-sm text-gray-600 text-right">{{ $count }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- お気に入りジャンル -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4">❤️ お気に入りジャンル</h3>
                        @if ($stats['favorite_genres']->isNotEmpty())
                            <div class="space-y-3">
                                @foreach ($stats['favorite_genres'] as $genre => $count)
                                    @php
                                        $maxCount = $stats['favorite_genres']->first() ?: 1;
                                        $percentage = ($count / $maxCount) * 100;
                                    @endphp
                                    <div class="flex items-center">
                                        <div class="w-24 text-sm font-medium truncate">{{ $genre }}</div>
                                        <div class="flex-1 mx-2">
                                            <div class="bg-gray-200 rounded-full h-4">
                                                <div class="bg-pink-400 h-4 rounded-full"
                                                    style="width: {{ $percentage }}%"></div>
                                            </div>
                                        </div>
                                        <div class="w-8 text-sm text-gray-600 text-right">{{ $count }}</div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-500">お気に入り書籍がありません。</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- ジャンル別統計 -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">📚 ジャンル別レビュー統計</h3>
                    @if ($stats['genre_stats']->isNotEmpty())
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            ジャンル</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            レビュー数</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            平均評価</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($stats['genre_stats'] as $genre)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ $genre['name'] }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $genre['count'] }}件</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <span class="text-yellow-500">
                                                    @for ($i = 1; $i <= 5; $i++)
                                                        @if ($i <= round($genre['average_rating']))
                                                        ★@else☆
                                                        @endif
                                                    @endfor
                                                </span>
                                                ({{ $genre['average_rating'] }})
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-gray-500">レビューがありません。</p>
                    @endif
                </div>
            </div>

            <!-- 高評価書籍 -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">🏆 高評価書籍（4星以上）</h3>
                    @if ($stats['top_rated_books']->isNotEmpty())
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach ($stats['top_rated_books'] as $book)
                                <div class="border rounded-lg p-4">
                                    <h4 class="font-semibold text-gray-900">{{ $book['title'] }}</h4>
                                    <p class="text-sm text-gray-600">{{ $book['author'] }}</p>
                                    <div class="flex items-center justify-between mt-2">
                                        <span class="text-yellow-500">
                                            @for ($i = 1; $i <= 5; $i++)
                                                @if ($i <= $book['rating'])
                                                ★@else☆
                                                @endif
                                            @endfor
                                        </span>
                                        <span class="text-xs text-gray-400">{{ $book['reviewed_at'] }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500">高評価書籍がありません。</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
--}}
