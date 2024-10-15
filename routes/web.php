<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\RegisteredUserController;
use Illuminate\Support\Facades\Route;
// use Illuminate\Http\Request;
use App\Http\Controllers\BookController;
use App\Http\Controllers\DataAnalysisController;
use App\Http\Controllers\FishPriceController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\FishPriceTrendController;
use App\Http\Controllers\CsvImportController;  // 追加 CSVインポート用コントローラー
use App\Http\Controllers\LineBotController; // 追加
use App\Http\Controllers\SettingController; // 追加
use App\Http\Controllers\CommentController; // 追加


Route::post('webhook/linebot', [LineBotController::class, 'reply']);

Route::middleware(['auth'])->group(function () {

    Route::get('/', [BookController::class,'index'])->middleware(['auth'])->name('dashboard');
    Route::get('/dashboard', [BookController::class,'index'])->middleware(['auth'])->name('dashboard');

    // コメント関連のルート
    Route::get('/comments', [CommentController::class, 'index'])->name('comments.index');
    Route::post('/comments', [CommentController::class, 'store'])->name('comments.store');
    
    // 入力画面
    Route::get('/input', [FishPriceController::class, 'create'])->name('fish_price.create');
    Route::post('/input', [FishPriceController::class, 'store'])->name('fish_price.store');

    // データ分析画面
    Route::get('/data-analysis', [DataAnalysisController::class, 'index'])->name('data.analysis');

    // フィッシュ価格トレンドAPI ルート
    Route::get('/api/analysis/fish-average-prices', [FishPriceTrendController::class, 'getAveragePrices']);
    
    // 分析用API ルート
    Route::get('/api/analysis/fish-prices', [DataAnalysisController::class, 'getAnalysisData'])->name('api.analysis.fish-prices');
    Route::get('/api/analysis/fish-types', [DataAnalysisController::class, 'getFishTypes'])->name('api.analysis.fish-types');
    Route::get('/api/fish-purchase-total', [FishPriceTrendController::class, 'getFishPurchaseTotal']);

    // データ一覧画面
    Route::get('/data-list', [FishPriceController::class, 'index'])->name('data.list');

    // API ルート
    Route::get('/api/fish-data', [FishPriceController::class, 'getData']);
    Route::post('/api/delete-fish-data', [FishPriceController::class, 'deleteData']);

    // データ更新画面
    Route::get('/data-update/{id}', [FishPriceController::class, 'edit'])->name('data.edit');
    Route::put('/data-update/{id}', [FishPriceController::class, 'update'])->name('fish_price.update');

    // ソフトデリートAPI ルート
    Route::post('/api/soft-delete-fish-data', [FishPriceController::class, 'softDelete']);

    // 消費期限切れの商品を取得するためのルート
    Route::post('/api/confirm-expiry/{id}', [FishPriceController::class, 'confirmExpiry'])->name('api.confirm-expiry');
    Route::get('/api/expiry-alerts', [FishPriceController::class, 'getExpiryAlerts'])->name('api.expiry-alerts');

    // ユーザーパスワード リセットリンク関連のルート
    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
                    ->middleware('guest')
                    ->name('password.request');
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
                    ->middleware('guest')
                    ->name('password.email');

});


// 管理者用のルート
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::patch('/settings/toggle-life/{user}', [SettingController::class, 'toggleLife'])->name('settings.toggle-life');
    Route::patch('/settings/toggle-admin/{user}', [SettingController::class, 'toggleAdmin'])->name('settings.toggle-admin');
});

// // 本関連のルート
// Route::middleware(['auth'])->group(function () {
//     Route::post('/books', [BookController::class, "store"])->name('book_store');
//     Route::delete('/book/{book}', [BookController::class, "destroy"])->name('book_destroy');
//     Route::post('/booksedit/{book}', [BookController::class, "edit"])->name('book_edit');
//     Route::get('/booksedit/{book}', [BookController::class, "edit"])->name('edit');
//     Route::post('/books/update', [BookController::class, "update"])->name('book_update');
// });

// プロフィール関連のルート
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('register', [RegisteredUserController::class, 'store']);
});

// test
Route::get('/upload-csv', [CsvImportController::class, 'showUploadForm'])->name('show.upload.form');
Route::post('/upload-csv', [CsvImportController::class, 'import'])->name('upload.csv');
Route::get('/test', [CsvImportController::class, 'showTest'])->name('show.test');