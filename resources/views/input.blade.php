@extends('layouts.app')

@section('title', 'おさかなハぅマっチ？ - データ入力')

@section('additional_styles')
<style>
    /* カスタムスタイル（Tailwindで対応できない部分） */
    .preview {
        background-size: contain;
        background-repeat: no-repeat;
        background-position: center;
    }
</style>
@endsection

@section('content')
<!--<div class="container mx-auto px-4">-->
    <!--<div class="bg-white rounded-3xl shadow-lg p-8 transition duration-300 ease-in-out hover:shadow-xl hover:-translate-y-1">-->
        <h2 class="text-2xl font-bold mb-6 text-primary relative pb-3 after:content-[''] after:absolute after:left-0 after:bottom-0 after:w-12 after:h-1 after:bg-secondary after:rounded-full">データ入力</h2>
        {{-- <!-- ダミーアップロードボタン -->
        <button class="w-2/12 bg-gradient-to-r from-primary to-primary-dark text-white font-bold py-3 px-4 rounded-lg transition duration-300 hover:opacity-80 hover:-translate-y-1 transform">CSVアップロード</button>
        <p class="text-sm text-gray-500 mt-2">※ CSVファイルをアップロードして一括登録することができます。</p>
        <!-- ダミーアップロードボタン --> --}}
        <form id="fishPriceForm" action="{{ route('fish_price.store') }}" method="post" enctype="multipart/form-data" class="space-y-6">
            @csrf
            <!-- 既存のフィールド -->
            <div>
                <label for="date" class="block mb-2 font-bold">日付 *</label>
                <input id="date" class="w-full p-3 border-2 border-input-border rounded-lg focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50 transition duration-300" type="date" name="date" required>
            </div>
            <div>
                <label for="fish" class="block mb-2 font-bold">魚種 *</label>
                <select id="fish" class="w-full p-3 border-2 border-input-border rounded-lg focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50 transition duration-300 appearance-none bg-white bg-no-repeat bg-right" style="background-image: url('data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'12\' height=\'12\' viewBox=\'0 0 12 12\'%3E%3Cpath fill=\'%23333\' d=\'M10.293 3.293L6 7.586 1.707 3.293A1 1 0 00.293 4.707l5 5a1 1 0 001.414 0l5-5a1 1 0 10-1.414-1.414z\'/%3E%3C/svg%3E');" name="fish" required>
                    <option value="" disabled selected>選択してください</option>
                    <option>ハマチ</option>
                    <option>マグロ</option>
                    <option>サバ</option>  
                    <option>アジ</option>
                </select>
            </div>
            <div>
                <label for="place" class="block mb-2 font-bold">産地</label>
                <select id="place" class="w-full p-3 border-2 border-input-border rounded-lg focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50 transition duration-300 appearance-none bg-white bg-no-repeat bg-right" style="background-image: url('data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'12\' height=\'12\' viewBox=\'0 0 12 12\'%3E%3Cpath fill=\'%23333\' d=\'M10.293 3.293L6 7.586 1.707 3.293A1 1 0 00.293 4.707l5 5a1 1 0 001.414 0l5-5a1 1 0 10-1.414-1.414z\'/%3E%3C/svg%3E');" name="place">
                    <option value="" disabled selected>選択してください</option>
                    <option>北海道</option>
                    <option>江戸前</option>
                    <option>九州</option>
                </select>
            </div>
            <div>
                <label for="price" class="block mb-2 font-bold">仕入価格 (円/kg) *</label>
                <input id="price" class="w-full p-3 border-2 border-input-border rounded-lg focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50 transition duration-300" type="number" placeholder="金額を入力" name="price" required>
            </div>
            <!-- 新しいフィールド：販売単価 -->
            <div>
                <label for="selling_price" class="block mb-2 font-bold">販売単価 (円/kg)</label>
                <input id="selling_price" class="w-full p-3 border-2 border-input-border rounded-lg focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50 transition duration-300" type="number" placeholder="販売単価を入力" name="selling_price">
            </div>
            <!-- 新しいフィールド：販売数量 -->
            <div>
                <label for="quantity_sold" class="block mb-2 font-bold">販売数量 (kg)</label>
                <input id="quantity_sold" class="w-full p-3 border-2 border-input-border rounded-lg focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50 transition duration-300" type="number" placeholder="販売数量を入力" name="quantity_sold">
            </div>
            <!-- 既存のフィールド（続き） -->
            <div>
                <label for="remarks" class="block mb-2 font-bold">メモ</label>
                <textarea id="remarks" class="w-full p-3 border-2 border-input-border rounded-lg focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50 transition duration-300 resize-y" maxlength="200" name="remarks" placeholder="200文字以内"></textarea>
                <div class="flex justify-between text-sm text-gray-500 mt-1">
                    <span id="charCount">0/200</span>
                    <div id="error-message" class="text-red-500 hidden">200文字を超えています</div>
                </div>
            </div>
            <div>
                <label for="imgFile" class="block mb-2 font-bold cursor-pointer bg-gradient-to-r from-primary to-primary-dark text-white py-3 px-4 rounded-lg transition duration-300 hover:opacity-80">
                    写真を選択
                </label>
                <input accept="image/*" id="imgFile" type="file" name="imgFile" class="hidden">
                <div class="preview h-64 mt-4 border-2 border-input-border rounded-lg"></div>
            </div>
            <button type="submit" class="w-full bg-gradient-to-r from-primary to-primary-dark text-white font-bold py-3 px-4 rounded-lg transition duration-300 hover:opacity-80 hover:-translate-y-1 transform">データ保存</button>
        </form>
    </div>
</div>
@endsection

@section('additional_scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const textarea = document.getElementById('remarks');
        const charCount = document.getElementById('charCount');
        const errorMessage = document.getElementById('error-message');

        textarea.addEventListener('input', function() {
            const count = this.value.length;
            charCount.textContent = `${count}/200`;
            
            if (count > 200) {
                errorMessage.classList.remove('hidden');
            } else {
                errorMessage.classList.add('hidden');
            }
        });

        const imgFile = document.getElementById('imgFile');
        const preview = document.querySelector('.preview');

        imgFile.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.style.backgroundImage = `url('${e.target.result}')`;
                }
                reader.readAsDataURL(file);
            }
        });
    });
</script>
@endsection