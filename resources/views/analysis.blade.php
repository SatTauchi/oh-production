@extends('layouts.app')

@section('title', 'おさかなハぅマっチ？ - データ分析')

@section('additional_styles')
<style>
    .dashboard-card {
        width: 100%;
        height: 400px;
        min-width: 300px;
        min-height: 300px;
    }
    .chart-container {
        width: 100%;
        height: calc(100% - 60px);
    }
    .fish-select-container {
        width: 200px;
        margin: 0 auto 1rem;
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
    .dashboard-card::after {
        content: '';
        position: absolute;
        bottom: 10px;
        right: 10px;
        width: 15px;
        height: 15px;
        background: url('data:image/svg+xml;charset=UTF-8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24"><path fill="gray" d="M0 0h24v24H0z" fill="none"/><path fill="currentColor" d="M15 1h-2v2h2v-2zm-4 0h-2v2h2v-2zm6 0h-2v2h2v-2zm2 18h-2v2h2v-2zm-4 0h-2v2h2v-2zm4-8h-2v2h2v-2zm0-4h-2v2h2v-2zm-8 16h-2v2h2v-2zm-4 0h-2v2h2v-2zm4-8h-2v2h2v-2zm-8 8h-2v2h2v-2zm0-8h-2v2h2v-2zm0-4h-2v2h2v-2zm0-8h-2v2h2v-2zm0-4h-2v2h2v-2zm4 0h-2v2h2v-2zm4 0h-2v2h2v-2zm8 8h-2v2h2v-2zm0-8h-2v2h2v-2zm-8 8h-2v2h2v-2zm8 0h-2v2h2v-2zm-16 0h-2v2h2v-2zm0 4h-2v2h2v-2zm0 4h-2v2h2v-2zm0-16h-2v2h2v-2zm4 8h-2v2h2v-2zm0-8h-2v2h2v-2zm-4 8h-2v2h2v-2z"/></svg>') no-repeat center center;
        cursor: nwse-resize;
    }
</style>
@endsection

@section('content')
<div class="container mx-auto px-4">
    <div class="bg-white rounded-3xl shadow-lg p-8 transition duration-300 ease-in-out hover:shadow-xl mb-8">
        <h2 class="text-2xl font-bold mb-6 text-primary relative pb-3 after:content-[''] after:absolute after:left-0 after:bottom-0 after:w-12 after:h-1 after:bg-blue-500 after:rounded-full">データ分析</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @for ($i = 1; $i <= 6; $i++)
                <div id="card{{ $i }}" class="dashboard-card bg-white rounded-3xl shadow-lg p-6 text-center transition duration-300 ease-in-out cursor-move resize overflow-hidden border border-gray-200" draggable="true">
                    <div class="fish-select-container">
                        <select id="fish-select{{ $i }}" class="fish-select">
                            <option value="">魚を選択して下さい</option>
                            <option value="all">全データ表示</option>
                        </select>
                    </div>
                    <div class="chart-container">
                        <canvas id="priceChart{{ $i }}"></canvas>
                    </div>
                </div>
            @endfor
        </div>
    </div>
</div>
@endsection

@section('additional_scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    function fetchFishTypes() {
        fetch('/api/analysis/fish-types')
            .then(response => response.json())
            .then(fishTypes => {
                const selects = document.querySelectorAll('.fish-select');
                selects.forEach(select => {
                    fishTypes.forEach(fish => {
                        const option = document.createElement('option');
                        option.value = fish;
                        option.textContent = fish;
                        select.appendChild(option);
                    });
                });
            })
            .catch(error => console.error('Error:', error));
    }

    function fetchDataAndDrawChart(selectId, chartId) {
        document.getElementById(selectId).addEventListener('change', function() {
            const selectedFish = this.value;
            if (selectedFish !== "") {
                fetch(`/api/analysis/fish-prices?fish=${selectedFish === "all" ? "" : selectedFish}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.length === 0) {
                            alert('選択された魚のデータがありません。');
                        } else {
                            let dates = data.map(item => item.date);
                            let prices = data.map(item => item.price);
                            drawChart(chartId, dates, prices, selectedFish);
                        }
                    })
                    .catch(() => {
                        alert('データの取得に失敗しました。');
                    });
            } else {
                alert('魚を選択してください');
            }
        });
    }

    let charts = {};

    function drawChart(chartId, dates, prices, fishName) {
        const ctx = document.getElementById(chartId).getContext('2d');
        
        if (charts[chartId]) {
            charts[chartId].destroy();
        }

        const colors = {
            'priceChart1': { border: 'rgba(255, 0, 0, 0.3)', background: 'rgba(255, 0, 0, 0.1)' },
            'priceChart2': { border: 'rgba(0, 0, 255, 0.3)', background: 'rgba(0, 0, 255, 0.1)' },
            'priceChart3': { border: 'rgba(0, 255, 0, 0.3)', background: 'rgba(0, 255, 0, 0.1)' },
            'priceChart4': { border: 'rgba(255, 165, 0, 0.3)', background: 'rgba(255, 165, 0, 0.1)' },
            'priceChart5': { border: 'rgba(128, 0, 128, 0.3)', background: 'rgba(128, 0, 128, 0.1)' },
            'priceChart6': { border: 'rgba(255, 192, 203, 0.3)', background: 'rgba(255, 192, 203, 0.1)' },
        };

        // 日付を YYYY-MM-DD 形式にフォーマット
        const formattedDates = dates.map(date => {
            const d = new Date(date);
            return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
        });

        charts[chartId] = new Chart(ctx, {
            type: 'line',
            data: {
                labels: formattedDates,
                datasets: [{
                    label: `${fishName === 'all' ? '全魚種' : fishName}の価格 (円/kg)`,
                    data: prices,
                    borderColor: colors[chartId].border,
                    backgroundColor: colors[chartId].background,
                    borderWidth: 3,
                    fill: true
                }]
            },
            options: {
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: '日付'
                        },
                        grid: {
                            borderWidth: 2
                        },
                        ticks: {
                            maxRotation: 45,
                            minRotation: 45
                        }
                    },
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: '価格 (円/kg)'
                        },
                        grid: {
                            borderWidth: 2
                        }
                    }
                },
                responsive: true,
                maintainAspectRatio: false
            }
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        fetchFishTypes();
        for (let i = 1; i <= 6; i++) {
            fetchDataAndDrawChart(`fish-select${i}`, `priceChart${i}`);
        }
        
        // すべてのチャートをリサイズ
        Object.keys(charts).forEach(chartId => {
            updateChart(chartId);
        });
    });

    // ドラッグ&ドロップの実装
    document.querySelectorAll('.dashboard-card').forEach(element => {
        element.ondragstart = function(event) {
            event.dataTransfer.setData('text/plain', event.target.id);
        };

        element.ondragover = function(event) {
            event.preventDefault();
        };

        element.ondragenter = function(event) {
            this.classList.add('border-2', 'border-dashed', 'border-gray-400');
        };

        element.ondragleave = function() {
            this.classList.remove('border-2', 'border-dashed', 'border-gray-400');
        };

        element.ondrop = function(event) {
            event.preventDefault();
            this.classList.remove('border-2', 'border-dashed', 'border-gray-400');

            let draggedElementId = event.dataTransfer.getData('text');
            let draggedElement = document.getElementById(draggedElementId);
            let dropTarget = event.currentTarget;

            if (draggedElement !== dropTarget) {
                let dropTargetNextSibling = dropTarget.nextElementSibling;
                let draggedElementParent = draggedElement.parentNode;

                draggedElementParent.insertBefore(dropTarget, draggedElement);

                if (dropTargetNextSibling) {
                    draggedElementParent.insertBefore(draggedElement, dropTargetNextSibling);
                } else {
                    draggedElementParent.appendChild(draggedElement);
                }
            }
        };
    });

    // リサイズの実装
    document.querySelectorAll('.dashboard-card').forEach(card => {
        let isResizing = false;
        let startX, startY, startWidth, startHeight;

        card.addEventListener('mousedown', initResize, false);

        function initResize(e) {
            if (e.target === card) return; // カード全体のドラッグを防ぐ
            if (e.offsetX > card.offsetWidth - 15 && e.offsetY > card.offsetHeight - 15) {
                isResizing = true;
                startX = e.clientX;
                startY = e.clientY;
                startWidth = parseInt(document.defaultView.getComputedStyle(card).width, 10);
                startHeight = parseInt(document.defaultView.getComputedStyle(card).height, 10);
                document.addEventListener('mousemove', resize, false);
                document.addEventListener('mouseup', stopResize, false);
            }
        }

        function resize(e) {
            if (isResizing) {
                const newWidth = Math.max(300, startWidth + e.clientX - startX);
                const newHeight = Math.max(300, startHeight + e.clientY - startY);
                card.style.width = newWidth + 'px';
                card.style.height = newHeight + 'px';
                updateChart(card.querySelector('canvas').id);
            }
        }

        function stopResize() {
            isResizing = false;
            document.removeEventListener('mousemove', resize, false);
            document.removeEventListener('mouseup', stopResize, false);
        }
    });

    // チャートの更新関数
    function updateChart(chartId) {
        if (charts[chartId]) {
            charts[chartId].resize();
        }
    }
</script>
@endsection