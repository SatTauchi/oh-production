@extends('layouts.app')

@section('title', 'おさかなハぅマっチ？ - データ一覧')

@section('content')
<!--<div class="container mx-auto px-4">-->
    <!--<div class="bg-white rounded-3xl shadow-lg p-8 transition duration-300 ease-in-out hover:shadow-xl mb-8">-->
        <h2 class="text-2xl font-bold mb-6 text-primary relative pb-3 after:content-[''] after:absolute after:left-0 after:bottom-0 after:w-12 after:h-1 after:bg-secondary after:rounded-full">データ一覧</h2>
        <div id="button02" class="flex flex-col sm:flex-row justify-between items-center gap-4 mb-6">
            <select id="fish-select" class="w-full sm:w-auto sm:min-w-[200px] px-4 py-2 border-2 border-primary text-primary font-bold rounded-full transition duration-300 hover:bg-primary hover:text-white focus:outline-none focus:ring-2 focus:ring-primary focus:ring-opacity-50">
                {{-- <option value="">魚を選択して下さい</option> でフォルをを全データ表示に変更 --}}
                <option value="all">全データ表示</option>
                <option value="ハマチ">ハマチ</option>
                <option value="マグロ">マグロ</option>
                <option value="サバ">サバ</option>
                <option value="アジ">アジ</option>
            </select>
            <button id="fetch-data" class="w-full sm:w-auto sm:min-w-[200px] px-4 py-2 border-2 border-primary text-primary font-bold rounded-full transition duration-300 hover:bg-primary hover:text-white focus:outline-none focus:ring-2 focus:ring-primary focus:ring-opacity-50">データを見る</button>
            <a href="{{ route('dashboard') }}" class="w-full sm:w-auto sm:min-w-[200px] px-4 py-2 border-2 border-primary text-primary font-bold rounded-full transition duration-300 hover:bg-primary hover:text-white focus:outline-none focus:ring-2 focus:ring-primary focus:ring-opacity-50 text-center">戻る</a>
        </div>
        <div id="list" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- データがここに表示される -->
        </div>
    </div>
</div>
@endsection

@section('additional_scripts')
<script>window.apiBaseUrl = "{{ config('app.api_base_url') }}";</script>
<script>
    const API_BASE_URL = window.apiBaseUrl;
    
    document.addEventListener('DOMContentLoaded', function() {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (!csrfToken) {
            console.error('CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token');
        }

        document.getElementById('fetch-data').addEventListener('click', function() {
            const selectedFish = document.getElementById('fish-select').value;
            if (selectedFish !== "") {
                fetch(`${API_BASE_URL}/api/fish-data?fish=${selectedFish === "all" ? "" : selectedFish}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.error) {
                            alert(data.error);
                        } else {
                            let output = '';
                            data.forEach(function(item) {
                                output += `
                                    <div class="dashboard-card bg-white rounded-3xl shadow-lg p-6 transition duration-300 ease-in-out hover:shadow-xl hover:-translate-y-1 flex flex-col justify-between relative" data-id="${item.id}">
                                        <img src="${item.photo ? item.photo : '/images/placeholder.jpg'}" alt="${item.fish}" class="w-full h-auto rounded-2xl mb-4">
                                        <p class="text-sm text-gray-600 mb-8"> <!-- mb-8 を追加してボタンのスペースを確保 -->
                                            日付：${item.date} <br> 
                                            魚：${item.fish} <br> 
                                            産地：${item.place} <br> 
                                            仕入単価：${item.price} 円/kg<br>
                                            販売単価：${item.selling_price ? item.selling_price + ' 円/kg' : '未設定'}<br>
                                            数量：${item.quantity_sold ? item.quantity_sold + ' kg' : '未設定'}<br>
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
                        }
                    })
                    .catch(() => {
                        alert('データの取得に失敗しました。');
                    });
            } else {
                alert('魚を選択してください');
            }
        });

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
    });
</script>
@endsection