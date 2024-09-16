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
    }
    .fish-select {
        width: 100%;
        padding: 0.5rem;
        border: 2px solid #3490dc;
        color: #3490dc;
        font-weight: bold;
        border-radius: 9999px;
        transition: all 0.3s;
        background-color: white;
    }
    .fish-select:hover {
        background-color: #3490dc;
        color: white;
    }
    .fish-select:focus {
        outline: none;
        box-shadow: 0 0 0 3px rgba(52, 144, 220, 0.5);
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
</style>
@endsection

@section('content')
<div class="container mx-auto px-4">
    <div class="bg-white rounded-3xl shadow-lg p-8 transition duration-300 ease-in-out hover:shadow-xl mb-8">
        <h2 class="text-2xl font-bold mb-6 text-primary relative pb-3 after:content-[''] after:absolute after:left-0 after:bottom-0 after:w-12 after:h-1 after:bg-blue-500 after:rounded-full">ダッシュボード</h2>
        <div class="grid grid-cols-1 md:grid-cols-1 gap-6">
            <div class="dashboard-card-pricetrend bg-white rounded-3xl shadow-lg p-6 transition duration-300 ease-in-out">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold text-primary">価格推移</h3>
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
                <h3 class="text-xl font-bold mb-4 text-primary">仕入割合</h3>
                <div class="chart-container flex-grow relative w-full" style="height: 300px;">
                    <canvas id="pie-chart" class="w-full h-full"></canvas>
                </div>
            </div>
            <div class="bg-white rounded-3xl shadow-lg p-6 transition duration-300 ease-in-out hover:shadow-xl">
                <h3 class="text-xl font-bold mb-4 text-primary">最新のコメント</h3>
                <ul class="space-y-4" id="recent-comments">
                    <!-- 新着コメントがここに動的に挿入されます -->
                    <div class="bg-white rounded-3xl shadow-lg p-6 transition duration-300 ease-in-out hover:shadow-xl">
                        <p class="text-sm text-gray-600">9-04 台風10号の影響で入荷が全般的に少ない</p>
                    </div>
                    <div class="bg-white rounded-3xl shadow-lg p-6 transition duration-300 ease-in-out hover:shadow-xl">
                        <p class="text-sm text-gray-600">9-05 仲買情報では今年は水温が高くカツオが豊漁</p>
                    </div>
                    <div class="bg-white rounded-3xl shadow-lg p-6 transition duration-300 ease-in-out hover:shadow-xl">
                        <p class="text-sm text-gray-600">9-14 なんか並のプロダクトと化しています </p>
                    </div>
                </ul>
            </div>
        </div>

        <!-- 新着データセクション -->
        <div class="mt-6">
            <h3 class="text-xl font-bold mb-4 text-primary">最新の入力データ</h3>
            <div id="list" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- 新着データカードがここに動的に挿入されます -->
            </div>
        </div>
    </div>
</div>
@endsection

@section('additional_scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/ja.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
<script>
const API_BASE_URL = 'https://bluebat2024.sakura.ne.jp/Osakana_Howmuch';

document.addEventListener('DOMContentLoaded', function() {
    let priceChart, piechart;
    const colorMap = {
        'ハマチ': '#4ECDC4',  // ターコイズ
        'マグロ': '#FF6B6B',  // 鮮やかな赤
        'サバ': '#45B7D1',    // 明るい青
        'アジ': '#FFA07A',    // ライトサーモン
        'タイ': '#98D8C8',    // ミントグリーン
        'サーモン': '#FFBE76', // パステルオレンジ
        'イワシ': '#A8D8EA',  // ライトスカイブルー
        'カツオ': '#FF8C94',  // ライトコーラル
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
                        return ''; // タイトル（日付）を空文字列に設定
                    },
                    label: function(context) {
                        let label = context.dataset.label || '';
                        if (label) {
                            label += ': ';
                        }
                        if (context.parsed.y !== null) {
                            label += new Intl.NumberFormat('ja-JP', { style: 'currency', currency: 'JPY' }).format(context.parsed.y);
                            if (context.datasetIndex <= 1) { // 仕入価格と販売価格の場合
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
                label: `${fishName}の平均仕入価格 (円/kg)`,
                data: prices,
                borderColor: colorMap[fishName] || getRandomColor(),
                backgroundColor: `${colorMap[fishName]}33` || getRandomColor(0.1),
                borderWidth: 3,
                fill: false,
                tension: 0,
                yAxisID: 'y-axis-1'
            },
            {
                label: `${fishName}の平均販売価格 (円/kg)`,
                data: sellingPrices,
                borderColor: adjustColor(colorMap[fishName] || getRandomColor(), -30),
                backgroundColor: adjustColor(colorMap[fishName] || getRandomColor(0.1), -30),
                borderWidth: 3,
                fill: false,
                tension: 0,
                yAxisID: 'y-axis-1'
            },
            {
                label: `${fishName}の利益額 (円)`,
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
    fetch('${API_BASE_URL}/api/analysis/fish-types')
        .then(response => response.json())
        .then(fishTypes => {
            const select = document.getElementById('fish-select');
            // select.innerHTML = '<option value="">魚を選択して下さい</option>';
            fishTypes.forEach(fish => {
                const option = document.createElement('option');
                option.value = fish;
                option.textContent = fish;
                if (fish === 'ハマチ') {
                    option.selected = true;
                }
                select.appendChild(option);
            });
            // フィッシュタイプの取得後、デフォルトデータを読み込む
            loadDefaultFishData();
        })
        .catch(error => {
            console.error('Error:', error);
            // エラーが発生しても、デフォルトデータを読み込もうとする
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
        // セレクトボックスの値を 'ハマチ' に設定（既に設定されている可能性もあるが、念のため）
        const selectElement = document.getElementById('fish-select');
        if (selectElement.value !== defaultFish) {
            selectElement.value = defaultFish;
        }
    }

    function loadFishData(fishName) {
        fetch('${API_BASE_URL}/api/analysis/fish-average-prices?fish=' + fishName)
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
        fetch('${API_BASE_URL}/api/fish-purchase-total')
            .then(response => response.json())
            .then(data => {
                const ctx = document.getElementById('pie-chart').getContext('2d');
                if (piechart) {
                    piechart.destroy();
                }
                
                // データの合計を計算
                const sum = data.data.reduce((acc, val) => acc + parseFloat(val), 0);
                
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
                                    const datapoint = ctx.chart.data.datasets[0].data[ctx.dataIndex];
                                    const percentage = ((parseFloat(datapoint) / sum) * 100).toFixed(1) + "%";
                                    return new Intl.NumberFormat('ja-JP', { style: 'currency', currency: 'JPY' }).format(datapoint) + '\n' + percentage;
                                },
                                textAlign: 'center'
                            },
                            legend: {
                                display: true,
                                position: 'right',
                                labels: {
                                    boxWidth: 12,
                                    padding: 10,
                                    font: {
                                        size: 10
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
    fetch('${API_BASE_URL}/api/fish-data?fish=')
        .then(response => response.json())
        .then(data => {
            let output = '';
            data.slice(0, 6).forEach(function(item) {
                output += `
                    <div class="dashboard-card bg-white rounded-3xl shadow-lg p-6 transition duration-300 ease-in-out hover:shadow-xl hover:-translate-y-1 flex flex-col justify-between relative" data-id="${item.id}">
                        <img src="${item.photo ? item.photo : '/images/placeholder.jpg'}" alt="${item.fish}" class="w-full h-auto rounded-2xl mb-4">
                        <p class="text-sm text-gray-600 mb-8"> <!-- mb-8 を追加してボタンのスペースを確保 -->
                            日付：${item.date} <br> 
                            魚：${item.fish} <br> 
                            産地：${item.place} <br> 
                            仕入金額：${item.price} 円/kg<br>
                            販売単価：${item.selling_price ? item.selling_price + ' 円/kg' : '未設定'}<br>
                            販売数量：${item.quantity_sold ? item.quantity_sold + ' kg' : '未設定'}<br>
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
                    window.location.href = `/data-update/${id}`;
                } else {
                    console.error('data-id attribute is missing for update button');
                }
            });
        });
    }

    function fetchRecentComments() {
        fetch('${API_BASE_URL}/api/recent-comments')
            .then(response => response.json())
            .then(comments => {
                const commentsList = document.getElementById('recent-comments');
                comments.slice(0, 5).forEach(comment => {
                    const li = document.createElement('li');
                    li.className = 'pb-4 border-b border-gray-200 last:border-b-0';
                    li.innerHTML = `
                        <p class="text-sm text-gray-600">${comment.content}</p>
                        <p class="text-xs text-gray-400 mt-1">投稿者: ${comment.user} - ${comment.date}</p>
                    `;
                    commentsList.appendChild(li);
                });
            });
    }

    function getRandomColor(alpha = 1) {
        const hue = Math.floor(Math.random() * 360);
        return `hsla(${hue}, 70%, 60%, ${alpha})`;
    }

    function adjustColor(color, amount) {
        return '#' + color.replace(/^#/, '').replace(/../g, color => ('0'+Math.min(255, Math.max(0, parseInt(color, 16) + amount)).toString(16)).substr(-2));
    }

    fetchFishTypes();
    initializeChart();
    fetchDataAndDrawChart();
    drawPieChart();
    fetchRecentData();
    fetchRecentComments();
});
</script>
@endsection