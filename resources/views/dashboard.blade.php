@extends('layouts.app')

@section('title', 'ダッシュボード - おさかなハぅマっチ？')

@section('additional_styles')
<link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">
<style>
    .dashboard-card-pricetrend {
        width: 100%;
        min-width: auto;
        min-height: 500px;
        display: flex;
        flex-direction: column;
    }
    .dashboard-card {
        width: 100%;
        min-width: auto;
        min-height: 300px;
        display: flex;
        flex-direction: column;
    }
    .chart-container {
        width: 100%;
        flex-grow: 1;
    }
    
    .fish-select-container {
    width: 200px;
    position: relative;
    }

    .fish-select {
        width: 100%;
        padding: 0.5rem 2rem 0.5rem 0.5rem; /* 右側のパディングを増やして矢印のスペースを確保 */
        border: 2px solid #3490dc;
        color: #3490dc;
        font-weight: bold;
        border-radius: 9999px;
        transition: all 0.3s;
        background-color: white;
        font-size: 16px;
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        text-align: center; /* テキストを中央揃えに */
        text-align-last: center; /* Firefox用 */
    }

    .fish-select-container::after {
        content: '\25BC'; /* 下向き矢印 */
        position: absolute;
        top: 50%;
        right: 10px;
        transform: translateY(-50%);
        pointer-events: none; /* クリックイベントを通過させる */
        color: #3490dc;
    }

    .fish-select:hover {
        background-color: #3490dc;
        color: white;
    }

    .fish-select:focus {
        outline: none;
        box-shadow: 0 0 0 3px rgba(52, 144, 220, 0.5);
    }
    
    @media (max-width: 768px) {
        .fish-select-container {
            width: 150px;
        }
        .fish-select {
                padding: 0.3rem 1.5rem 0.3rem 0.3rem;
                font-size: 14px;
            }
        }

    @media (max-width: 480px) {
        .fish-select-container {
            width: 120px;
        }

        .fish-select {
            padding: 0.2rem 1.5rem 0.2rem 0.2rem;
            font-size: 12px;
        }
    }

    .data-card {
        display: flex;
        flex-direction: column;
        height: 100%;
        min-height: 400px;
    }
    .data-card-image {
        flex: 0 0 auto;
        max-height: 200px;
        overflow: hidden;
    }
    .data-card-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .data-card-content {
        flex: 1 1 auto;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        padding: 1rem;
    }
    #expiry-alerts-container {
        background: linear-gradient(135deg, #FFF5F5 0%, #FED7D7 100%);
        border: 1px solid #FEB2B2;
        box-shadow: 0 4px 6px rgba(254, 178, 178, 0.1);
    }

    #expiry-alerts-container h3 {
        color: #E53E3E;
    }

    .alert-item {
        background-color: rgba(255, 255, 255, 0.8);
        border: 1px solid #FC8181;
        border-left: 4px solid #E53E3E;
    }

    .alert-item p {
        color: #2D3748;
    }
    
    .confirm-btn {
        background-color: #E53E3E;
        color: white;
        transition: all 0.3s ease;
    }

    .confirm-btn:hover {
        background-color: #C53030;
        transform: translateY(-2px);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

</style>
@endsection

@section('content')
    <h2 class="text-2xl font-bold mb-6 text-primary relative pb-3 after:content-[''] after:absolute 
    after:left-0 after:bottom-0 after:w-12 after:h-1 after:bg-blue-500 after:rounded-full">ダッシュボード　（魚芳　中野支部）</h2>
    <div class="grid grid-cols-1 md:grid-cols-1 gap-6">
        <div class="dashboard-card-pricetrend bg-white rounded-3xl shadow-lg p-6 transition duration-300 ease-in-out">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold text-primary">自社の価格推移</h3>
                <div class="fish-select-container">
                    <select id="fish-select" class="fish-select"></select>
                </div>
            </div>
            <div class="chart-container">
                <canvas id="priceChart"></canvas>
            </div>
        </div>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
        <div class="dashboard-card bg-white rounded-3xl shadow-lg p-6 transition duration-300 ease-in-out hover:shadow-xl flex flex-col">
            <h3 class="text-xl font-bold mb-4 text-primary">自社の仕入割合</h3>
            <div class="chart-container flex-grow relative w-full" style="height: 300px;">
                <canvas id="pie-chart" class="w-full h-full"></canvas>
            </div>
        </div>
        <div class="bg-white rounded-3xl shadow-lg p-6 transition duration-300 ease-in-out hover:shadow-xl">
            <h3 class="text-xl font-bold mb-4 text-primary">中野支部　価格情報</h3>
            <ul class="space-y-4" id="recent-comments">
                <!-- 新着コメントがここに動的に挿入されます -->
            </ul>
            <form action="{{ route('comments.store') }}" method="POST" class="comment-form">
                @csrf
                <textarea name="content" class="w-full p-2 border border-gray-300 rounded" rows="3" placeholder="コメントを入力してください" required></textarea>
                <button type="submit" class="mt-2 bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition duration-300">投稿</button>
                @if (session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif
            </form>
        </div>
    </div>

    <!-- 消費期限アラートセクション -->
    <div id="expiry-alerts-container" class="rounded-3xl shadow-lg p-6 transition duration-300 ease-in-out hover:shadow-xl mt-6" style="min-height: 200px; overflow-y: auto;">
        <h3 class="text-xl font-bold mb-4 text-primary">消費期限アラート</h3>
        <div id="expiry-alerts">
            <!-- アラートがここに動的に挿入されます -->
        </div>
    </div>

    <!-- 新着データセクション -->
    <div class="mt-6">
        <h3 class="text-xl font-bold mb-4 text-primary">最新の入力データ</h3>
        <div id="list" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- 新着データカードがここに動的に挿入されます -->
        </div>
    </div>
@endsection

@push('scripts')
<script src="{{ asset('js/comments.js') }}"></script>
@endpush

@section('additional_scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/ja.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
<script>window.apiBaseUrl = "{{ config('app.api_base_url') }}";</script>
<script>
const API_BASE_URL = window.apiBaseUrl;
document.addEventListener('DOMContentLoaded', function() {
    let priceChart, piechart;
    const colorMap = {
        'ハマチ': '#4ECDC4',
        'マグロ': '#FF6B6B',
        'サバ': '#45B7D1',
        'アジ': '#FFA07A',
        'タイ': '#98D8C8',
        'サーモン': '#FFBE76',
        'イワシ': '#A8D8EA',
        'カツオ': '#FF8C94',
    };

    const chartOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: true
            },
            tooltip: {
                callbacks: {
                    title: function() {
                        return '';
                    },
                    label: function(context) {
                        let label = context.dataset.label || '';
                        if (label) {
                            label += ': ';
                        }
                        if (context.parsed.y !== null) {
                            label += new Intl.NumberFormat('ja-JP', { style: 'currency', currency: 'JPY' }).format(context.parsed.y);
                            if (context.datasetIndex <= 1) {
                                label += '/kg';
                            }
                        }
                        return label;
                    }
                }
            }
        },
        scales: {
            x: {
                type: 'time',
                time: {
                    unit: 'day',
                    displayFormats: {
                        day: 'yyyy-MM-dd'
                    }
                },
                grid: {
                    display: false
                },
                ticks: {
                    font: {
                        family: "'Helvetica Neue', 'Arial', sans-serif",
                        size: 11
                    }  
                },
                title: {
                    display: true,
                    text: '日付'
                },
                offset: true
            },
            'y-axis-1': {
                type: 'linear',
                position: 'left',
                beginAtZero: true,
                grid: {
                    color: 'rgba(0, 0, 0, 0.05)'
                },
                ticks: {
                    font: {
                        family: "'Helvetica Neue', 'Arial', sans-serif",
                        size: 11
                    }
                },
                title: {
                    display: true,
                    text: '価格 (円/kg)'
                }
            },
            'y-axis-2': {
                type: 'linear',
                position: 'right',
                beginAtZero: true,
                grid: {
                    drawOnChartArea: false
                },
                ticks: {
                    font: {
                        family: "'Helvetica Neue', 'Arial', sans-serif",
                        size: 11
                    }
                },
                title: {
                    display: true,
                    text: '利益額 (円)'
                }
            }
        }
    };

    function initializeChart() {
        const ctx = document.getElementById('priceChart').getContext('2d');
        
        priceChart = new Chart(ctx, {
            type: 'line',
            data: {
                datasets: []
            },
            options: chartOptions
        });
    }

    function updateChart(dates, prices, sellingPrices, profit, fishName) {
        const formattedDates = dates.map(date => new Date(date));

        priceChart.data.labels = formattedDates;
        priceChart.data.datasets = [
            {
                label: `${fishName}の仕入価格`,
                data: prices,
                borderColor: colorMap[fishName] || getRandomColor(),
                backgroundColor: `${colorMap[fishName]}33` || getRandomColor(0.1),
                borderWidth: 3,
                fill: false,
                tension: 0,
                yAxisID: 'y-axis-1'
            },
            {
                label: `${fishName}の販売価格`,
                data: sellingPrices,
                borderColor: adjustColor(colorMap[fishName] || getRandomColor(), -30),
                backgroundColor: adjustColor(colorMap[fishName] || getRandomColor(0.1), -30),
                borderWidth: 3,
                fill: false,
                tension: 0,
                yAxisID: 'y-axis-1'
            },
            {
                label: `${fishName}の利益額`,
                data: profit,
                type: 'bar',
                backgroundColor: adjustColor(colorMap[fishName] || getRandomColor(), 30, 0.7),
                borderColor: adjustColor(colorMap[fishName] || getRandomColor(), 30),
                borderWidth: 1,
                yAxisID: 'y-axis-2',
                barPercentage: 0.3,
                categoryPercentage: 0.8
            },
        ];
        priceChart.update();
    }

    function fetchFishTypes() {
        fetch(`${API_BASE_URL}/api/analysis/fish-types`)
            .then(response => response.json())
            .then(fishTypes => {
                const select = document.getElementById('fish-select');
                fishTypes.forEach(fish => {
                    const option = document.createElement('option');
                    option.value = fish;
                    option.textContent = fish;
                    if (fish === 'ハマチ') {
                        option.selected = true;
                    }
                    select.appendChild(option);
                });
                loadDefaultFishData();
            })
            .catch(error => {
                console.error('Error:', error);
                loadDefaultFishData();
            });
    }

    function fetchDataAndDrawChart() {
        const fishSelect = document.getElementById('fish-select');
        
        fishSelect.addEventListener('change', function() {
            const selectedFish = this.value;
            if (selectedFish !== "") {
                loadFishData(selectedFish);
            } else {
                priceChart.data.datasets = [];
                priceChart.update();
            }
        });
    }

    function loadDefaultFishData() {
        const defaultFish = 'ハマチ';
        loadFishData(defaultFish);
        const selectElement = document.getElementById('fish-select');
        if (selectElement.value !== defaultFish) {
            selectElement.value = defaultFish;
        }
    }

    function loadFishData(fishName) {
        fetch(`${API_BASE_URL}/api/analysis/fish-average-prices?fish=` + fishName)
            .then(response => response.json())
            .then(data => {
                if (data.length === 0) {
                    alert('選択された魚のデータがありません。');
                } else {
                    let dates = data.map(item => item.date);
                    let prices = data.map(item => item.average_price);
                    let sellingPrices = data.map(item => item.average_selling_price);
                    let profit = data.map(item => (item.average_selling_price - item.average_price) * item.quantity_sold);
                    updateChart(dates, prices, sellingPrices, profit, fishName);
                }
            })
            .catch(() => {
                alert('データの取得に失敗しました。');
            });
    }

    function drawPieChart() {
        fetch(`${API_BASE_URL}/api/fish-purchase-total`)
            .then(response => response.json())
            .then(data => {
                const ctx = document.getElementById('pie-chart').getContext('2d');
                if (piechart) {
                    piechart.destroy();
                }
                
                const sum = data.data.reduce((acc, val) => acc + parseFloat(val), 0);
                
                const sortedIndices = data.data
                    .map((value, index) => ({ value: parseFloat(value), index }))
                    .sort((a, b) => b.value - a.value)
                    .map(item => item.index);
                const top2Indices = new Set(sortedIndices.slice(0, 2));

                piechart = new Chart(ctx, {
                    type: 'pie',
                    data: {
                        labels: data.labels,
                        datasets: [{
                            data: data.data,
                            backgroundColor: data.labels.map(label => colorMap[label] || getRandomColor()),
                            borderColor: 'white',
                            borderWidth: 2
                        }]
                    },
                    plugins: [ChartDataLabels],
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        layout: {
                            padding: {
                                left: 10,
                                right: 10,
                                top: 0,
                                bottom: 0
                            }
                        },
                        plugins: {
                            datalabels: {
                                color: '#fff',
                                font: {
                                    weight: 'bold',
                                    size: 16
                                },
                                formatter: (value, ctx) => {
                                    const index = ctx.dataIndex;
                                    if (top2Indices.has(index)) {
                                        const datapoint = ctx.chart.data.datasets[0].data[index];
                                        const percentage = ((parseFloat(datapoint) / sum) * 100).toFixed(1) + "%";
                                        return new Intl.NumberFormat('ja-JP', { style: 'currency', currency: 'JPY' }).format(datapoint) + '\n' + percentage;
                                    } else {
                                        return null;
                                    }
                                },
                                textAlign: 'center'
                            },
                            legend: {
                                display: true,
                                position: 'bottom',
                                labels: {
                                    boxWidth: 12,
                                    padding: 20,
                                    font: {
                                        size: 11
                                    }
                                }
                            },
                            title: {
                                display: false,
                                text: '魚種別仕入れ総額',
                                font: {
                                    size: 16,
                                    weight: 'bold'
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        let label = context.label || '';
                                        if (label) {
                                            label += ': ';
                                        }
                                        if (context.parsed !== null) {
                                            label += new Intl.NumberFormat('ja-JP', { style: 'currency', currency: 'JPY' }).format(context.parsed);
                                        }
                                        return label;
                                    }
                                }
                            }
                        }
                    }
                });
            });
    }

    function fetchRecentData() {
        fetch(`${API_BASE_URL}/api/fish-data?fish=`)
            .then(response => response.json())
            .then(data => {
                let output = '';
                data.slice(0, 6).forEach(function(item) {
                    output += `
                        <div class="dashboard-card bg-white rounded-3xl shadow-lg p-6 transition duration-300 ease-in-out hover:shadow-xl hover:-translate-y-1 flex flex-col justify-between relative" data-id="${item.id}">
                            <img src="${item.photo ? item.photo : '/images/placeholder.jpg'}" alt="${item.fish}" class="w-full h-auto rounded-2xl mb-4">
                            <p class="text-sm text-gray-600 mb-8">
                                日付：${item.date} <br> 
                                魚：${item.fish} <br> 
                                産地：${item.place} <br> 
                                仕入単価：${item.price} 円/kg<br>
                                販売単価：${item.selling_price ? item.selling_price + ' 円/kg' : '未設定'}<br>
                                数量：${item.quantity_sold ? item.quantity_sold + ' kg' : '未設定'}<br>
                                消費期限：${item.expiry_date || '未設定'}<br>
                                メモ：${item.remarks}
                            </p>
                            <button class="renew absolute w-3/12 bottom-4 right-4 px-3 py-1 text-sm border border-primary text-primary font-bold rounded-full transition duration-300 hover:bg-primary hover:text-white focus:outline-none focus:ring-2 focus:ring-primary focus:ring-opacity-50" type="button" data-id="${item.id}">
                                編集
                            </button>
                        </div>
                    `;
                });
                document.getElementById('list').innerHTML = output;
                addEventListeners();
            })
            .catch(error => {
                console.error('Error:', error);
                alert('データの取得に失敗しました。');
            });
    }

    function addEventListeners() {
        document.querySelectorAll('.renew').forEach(function(button) {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                if (id) {
                    window.location.href = `${API_BASE_URL}/data-update/${id}`;
                } else {
                    console.error('data-id attribute is missing for update button');
                }
            });
        });
    }

    function fetchRecentComments() {
        fetch('/comments', {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(comments => {
            const commentsList = document.getElementById('recent-comments');
            commentsList.innerHTML = '';
            comments.forEach(comment => {
                const li = document.createElement('li');
                li.className = 'comment-item';
                li.innerHTML = `
                    <p class="comment-content">${escapeHtml(comment.content)}</p>
                    <p class="comment-meta">投稿者: ${escapeHtml(comment.user.name)} - ${new Date(comment.created_at).toLocaleString('ja-JP')}</p>
                `;
                commentsList.appendChild(li);
            });
        })
        .catch(error => {
            console.error('Error fetching comments:', error);
            document.getElementById('recent-comments').innerHTML = '<p class="text-red-500">コメントの取得に失敗しました。</p>';
        });
    }

    function postComment(content) {
        fetch('/comments', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            credentials: 'same-origin',
            body: JSON.stringify({ content: content })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(comment => {
            console.log('Comment posted successfully:', comment);
            fetchRecentComments();
            document.getElementById('comment-content').value = '';
        })
        .catch(error => {
            console.error('Error posting comment:', error);
            alert('コメントの投稿に失敗しました。');
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        fetchRecentComments();

        const commentForm = document.getElementById('comment-form');
        commentForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const content = document.getElementById('comment-content').value.trim();
            if (content) {
                postComment(content);
            }
        });
    });

    function escapeHtml(unsafe) {
        return unsafe
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
}
    function fetchExpiryAlerts() {
        fetch(`${API_BASE_URL}/api/expiry-alerts`)
            .then(response => response.json())
            .then(alerts => {
                const alertsContainer = document.getElementById('expiry-alerts');
                if (alerts.length === 0) {
                    alertsContainer.innerHTML = '<p>消費期限切れの商品はありません。</p>';
                } else {
                    let alertsHtml = '';
                    alerts.forEach(alert => {
                        alertsHtml += `
                            <div class="alert-item mb-4 p-4 border border-red-300 rounded-lg" data-id="${alert.id}">
                                <p class="font-bold">${alert.fish}</p>
                                <p>消費期限: ${alert.expiry_date}</p>
                                <p>仕入単価: ¥${alert.price.toLocaleString()}/kg</p>
                                <p class="text-red-600 font-semibold">値引き提案: ¥${alert.discount_price.toLocaleString()}/kg</p>
                                <p>数量: ${alert.quantity_sold}kg</p>
                                <button class="confirm-btn mt-2 bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                    確認済み
                                </button>
                            </div>
                        `;
                    });
                    alertsContainer.innerHTML = alertsHtml;
                }
                
                // コンテナのサイズを調整
                adjustContainerSize();

                // 確認ボタンにイベントリスナーを追加
                document.querySelectorAll('.confirm-btn').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const id = this.closest('.alert-item').dataset.id;
                        confirmExpiry(id);
                    });
                });
            })
            .catch(error => {
                console.error('Error fetching expiry alerts:', error);
                document.getElementById('expiry-alerts').innerHTML = '<p>アラート情報の取得に失敗しました。</p>';
            });
    }

    function adjustContainerSize() {
        const container = document.getElementById('expiry-alerts-container');
        const content = document.getElementById('expiry-alerts');
        const maxHeight = window.innerHeight * 0.6; // ビューポートの高さの60%を最大高さとする

        // コンテンツの高さが最大高さを超える場合、スクロール可能にする
        if (content.scrollHeight > maxHeight) {
            container.style.height = `${maxHeight}px`;
            container.style.overflowY = 'auto';
        } else {
            container.style.height = 'auto';
            container.style.overflowY = 'visible';
        }
    }

    function confirmExpiry(id) {
        fetch(`${API_BASE_URL}/api/confirm-expiry/${id}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // アラートを再取得して表示を更新
                fetchExpiryAlerts();
            }
        })
        .catch(error => console.error('Error confirming expiry:', error));
    }

    function getRandomColor(alpha = 1) {
        const hue = Math.floor(Math.random() * 360);
        return `hsla(${hue}, 70%, 60%, ${alpha})`;
    }

    function adjustColor(color, amount) {
        return '#' + color.replace(/^#/, '').replace(/../g, color => ('0'+Math.min(255, Math.max(0, parseInt(color, 16) + amount)).toString(16)).substr(-2));
    }

    // ウィンドウサイズが変更されたときにコンテナサイズを再調整
    window.addEventListener('resize', adjustContainerSize);

    fetchFishTypes();
    initializeChart();
    fetchDataAndDrawChart();
    drawPieChart();
    fetchRecentData();
    fetchRecentComments();
    fetchExpiryAlerts();
});
</script>
@endsection