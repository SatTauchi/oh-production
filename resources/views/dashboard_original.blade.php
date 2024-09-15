@extends('layouts.app')

@section('title', 'ダッシュボード - おさかなハぅマっチ？')

@section('content')
<div class="bg-card-background rounded-3xl shadow-lg p-7 mb-7 transition-all duration-300 hover:transform hover:-translate-y-1 hover:shadow-xl">
    <h2 class="text-2xl font-bold mb-6 text-primary relative pb-2.5 after:content-[''] after:absolute after:left-0 after:bottom-0 
    after:w-12 after:h-0.75 after:bg-secondary after:rounded">
        @if(auth()->user()->admin_flg == 1)
            管理者用
        @endif
        ダッシュボード
    </h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        @if(auth()->user()->admin_flg != 1)
            <a href="{{ route('fish_price.create') }}" class="dashboard-card bg-card-background rounded-3xl shadow-lg p-7 text-center transition-all duration-300 
            cursor-pointer hover:transform hover:-translate-y-1 hover:shadow-xl">
                <i class="fas fa-edit text-5xl text-primary mb-5"></i>
                <h3 class="text-xl font-bold mb-2.5 text-text-color">データ入力</h3>
                <p class="text-sm text-text-color opacity-80">新しいデータを登録します</p>
            </a>
            <a href="{{ route('data.analysis') }}" class="dashboard-card bg-card-background rounded-3xl shadow-lg p-7 text-center transition-all duration-300 
            cursor-pointer hover:transform hover:-translate-y-1 hover:shadow-xl">
                <i class="fas fa-chart-line text-5xl text-primary mb-5"></i>
                <h3 class="text-xl font-bold mb-2.5 text-text-color">データ分析</h3>
                <p class="text-sm text-text-color opacity-80">登録データを様々な形式で表示します</p>
            </a>
        @endif
        <a href="{{ route('data.list') }}" class="dashboard-card bg-card-background rounded-3xl shadow-lg p-7 text-center transition-all duration-300 
        cursor-pointer hover:transform hover:-translate-y-1 hover:shadow-xl">
            <i class="fas fa-database text-5xl text-primary mb-5"></i>
            <h3 class="text-xl font-bold mb-2.5 text-text-color">データ一覧</h3>
            <p class="text-sm text-text-color opacity-80">登録データを確認・更新します</p>
        </a>
        @if(auth()->user()->admin_flg == 1)
            <a href="#" class="dashboard-card bg-card-background rounded-3xl shadow-lg p-7 text-center transition-all duration-300 
            cursor-pointer hover:transform hover:-translate-y-1 hover:shadow-xl">
                <i class="fas fa-cog text-5xl text-primary mb-5"></i>
                <h3 class="text-xl font-bold mb-2.5 text-text-color">設定</h3>
                <p class="text-sm text-text-color opacity-80">アプリケーションの設定を変更します</p>
            </a>
        @endif
    </div>
</div>
@endsection

@section('additional_scripts')
<script>
    // ダッシュボード固有のJavaScriptがあれば、ここに追加
</script>
@endsection