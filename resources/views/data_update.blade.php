@extends('layouts.app')

@section('title', 'おさかなハぅマっチ？ - データ更新')

@section('content')
<!--<div class="container mx-auto px-4">-->
    <!-- フラッシュメッセージ表示領域 -->
    <div id="flash-message" class="hidden mb-4 p-4 rounded-lg"></div>

    <!--<div class="bg-gray-300 rounded-3xl shadow-lg p-8 transition duration-300 ease-in-out hover:shadow-xl mb-8">-->
        <h2 class="text-2xl font-bold mb-6 text-primary relative pb-3 after:content-[''] after:absolute after:left-0 after:bottom-0 after:w-12 after:h-1 after:bg-secondary after:rounded-full">データ更新</h2>
        <form id="fishPriceForm" action="{{ route('fish_price.update', $result['id']) }}" method="post" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <input type="hidden" name="id" value="{{ $result['id'] }}">
            <div class="mb-6">
                <label for="date" class="block mb-2 font-bold text-gray-700">日付</label>
                <input id="date" class="w-full p-3 border-2 border-blue-200 rounded-lg focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 transition duration-300" type="date" name="date" value="{{ $result['date'] }}" required>
            </div>
            <div class="mb-6">
                <label for="fish" class="block mb-2 font-bold text-gray-700">魚種</label>
                <select id="fish" class="w-full p-3 border-2 border-blue-200 rounded-lg focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 transition duration-300" name="fish" required>
                    <option value="ハマチ" {{ $result['fish'] == 'ハマチ' ? 'selected' : '' }}>ハマチ</option>
                    <option value="マグロ" {{ $result['fish'] == 'マグロ' ? 'selected' : '' }}>マグロ</option>
                    <option value="サバ" {{ $result['fish'] == 'サバ' ? 'selected' : '' }}>サバ</option>
                    <option value="アジ" {{ $result['fish'] == 'アジ' ? 'selected' : '' }}>アジ</option>
                </select>
            </div>
            <div class="mb-6">
                <label for="place" class="block mb-2 font-bold text-gray-700">産地</label>
                <select id="place" class="w-full p-3 border-2 border-blue-200 rounded-lg focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 transition duration-300" name="place">
                    <option value="北海道" {{ $result['place'] == '北海道' ? 'selected' : '' }}>北海道</option>
                    <option value="江戸前" {{ $result['place'] == '江戸前' ? 'selected' : '' }}>江戸前</option>
                    <option value="九州" {{ $result['place'] == '九州' ? 'selected' : '' }}>九州</option>
                </select>
            </div>
            <div class="mb-6">
                <label for="price" class="block mb-2 font-bold text-gray-700">仕入価格 (円/kg)</label>
                <input id="price" class="w-full p-3 border-2 border-blue-200 rounded-lg focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 transition duration-300" type="number" placeholder="金額を入力（円/kg）" name="price" value="{{ $result['price'] }}" required>
            </div>
            <div class="mb-6">
                <label for="selling_price" class="block mb-2 font-bold text-gray-700">販売単価 (円/kg)</label>
                <input id="selling_price" class="w-full p-3 border-2 border-blue-200 rounded-lg focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 transition duration-300" type="number" placeholder="販売単価を入力（円/kg）" name="selling_price" value="{{ $result['selling_price'] }}">
            </div>
            <div class="mb-6">
                <label for="quantity_sold" class="block mb-2 font-bold text-gray-700">販売数量 (kg)</label>
                <input id="quantity_sold" class="w-full p-3 border-2 border-blue-200 rounded-lg focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 transition duration-300" type="number" placeholder="販売数量を入力（kg）" name="quantity_sold" value="{{ $result['quantity_sold'] }}">
            </div>
            <div class="mb-6">
                <label for="remarks" class="block mb-2 font-bold text-gray-700">メモ</label>
                <textarea id="remarks" class="w-full p-3 border-2 border-blue-200 rounded-lg focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 transition duration-300 resize-y" maxlength="200" name="remarks" placeholder="200文字以内">{{ $result['remarks'] }}</textarea>
                <div class="flex justify-between text-sm text-gray-500 mt-1">
                    <span id="charCount">0/200</span>
                    <div id="error-message" class="text-red-500 hidden">200文字を超えています</div>
                </div>
            </div>
            <div class="mb-6">
                <div class="input_file">
                    <label for="imgFile" class="block mb-2 font-bold bg-blue-500 text-white py-2 px-4 rounded-lg cursor-pointer hover:bg-blue-600 transition duration-300">写真を選択</label>
                    <div class="preview_field mt-4 border-2 border-blue-200 rounded-lg p-4 flex items-center justify-center h-64">
                        <input accept="image/*" id="imgFile" type="file" name="imgFile" class="hidden"> 
                        <img id="currentImage" src="{{ $result['photo'] }}" alt="current image" class="max-h-full max-w-full object-contain">
                    </div>
                </div>
            </div>
            <div class="flex justify-between mt-4">
                <button type="submit" class="w-1/2 mr-2 bg-gradient-to-r from-blue-500 to-blue-700 text-white font-bold py-3 px-4 rounded-lg transition duration-300 hover:from-blue-600 hover:to-blue-800 transform hover:-translate-y-1 hover:shadow-lg">データ更新</button>
                <button type="button" id="delete-btn" class="w-1/2 ml-2 bg-gradient-to-r from-red-500 to-red-700 text-white font-bold py-3 px-4 rounded-lg transition duration-300 hover:from-red-600 hover:to-red-800 transform hover:-translate-y-1 hover:shadow-lg">削除</button>
            </div>
        </form>
        <button onclick="location.href='{{ route('data.list') }}'" class="w-full mt-4 bg-gray-500 text-white font-bold py-3 px-4 rounded-lg transition duration-300 hover:bg-gray-600 transform hover:-translate-y-1 hover:shadow-lg">戻る</button>
    </div>
</div>
@endsection

@section('additional_scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const textarea = document.getElementById('remarks');
        const charCount = document.getElementById('charCount');
        const errorMessage = document.getElementById('error-message');

        function updateCharCount() {
            const count = textarea.value.length;
            charCount.textContent = `${count}/200`;
            
            if (count > 200) {
                errorMessage.classList.remove('hidden');
            } else {
                errorMessage.classList.add('hidden');
            }
        }

        textarea.addEventListener('input', updateCharCount);
        updateCharCount(); // 初期表示時にも文字数を更新

        document.getElementById('imgFile').addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('currentImage').src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });

        // 削除ボタンのイベントリスナー
        document.getElementById('delete-btn').addEventListener('click', function() {
            if (confirm('このデータを削除しますか？')) {
                const id = {{ $result['id'] }};
                fetch('/api/soft-delete-fish-data', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ id: id })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showFlashMessage('データが正常に削除されました。', 'success');
                        setTimeout(() => {
                            window.location.href = '{{ route('data.list') }}';
                        }, 2000);
                    } else {
                        showFlashMessage('データの削除に失敗しました。', 'error');
                    }
                })
                .catch((error) => {
                    console.error('Error:', error);
                    showFlashMessage('データの削除に失敗しました。', 'error');
                });
            }
        });

        function showFlashMessage(message, type) {
            const flashElement = document.getElementById('flash-message');
            flashElement.textContent = message;
            flashElement.classList.remove('hidden', 'bg-green-100', 'text-green-700', 'bg-red-100', 'text-red-700');
            
            if (type === 'success') {
                flashElement.classList.add('bg-green-100', 'text-green-700');
            } else {
                flashElement.classList.add('bg-red-100', 'text-red-700');
            }

            flashElement.classList.remove('hidden');
            
            setTimeout(() => {
                flashElement.classList.add('hidden');
            }, 5000);
        }
    });
</script>
@endsection