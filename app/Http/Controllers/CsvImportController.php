<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use League\Csv\Reader;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class CsvImportController extends Controller
{
    public function showUploadForm()
    {
        return view('csv');
    }

    public function import(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt',
        ]);

        $file = $request->file('csv_file');
        
        $csv = Reader::createFromPath($file->getPathname(), 'r');
        $csv->setDelimiter("\t"); // タブ区切りに設定

        // 全行を取得
        $allRows = iterator_to_array($csv->getRecords());

        // ヘッダー行（1行目）
        $headers = $allRows[0];

        // データ行（2行目以降）を処理
        $records = [];
        for ($i = 1; $i < count($allRows); $i++) {
            $row = $allRows[$i];
            $record = [];
            foreach ($headers as $index => $header) {
                $record[$header] = $row[$index] ?? '';
            }
            $records[] = $record;
        }

        // データの整形
        $formattedRecords = $this->formatRecords($records);

        // セッションにCSVデータを保存
        Session::put('csv_data', $formattedRecords);
        
        // セッションへの保存をログに記録
        Log::info('CSV data saved to session', [
            'headers' => $headers,
            'data' => $formattedRecords,
            'session_id' => Session::getId(),
        ]);

        // セッションの保存を強制
        Session::save();

        // test.blade.phpにリダイレクト
        return redirect()->route('show.test')->with('debug_message', 'Redirected from import method');
    }

    private function formatRecords($records)
    {
        $formattedRecords = [];
        foreach ($records as $record) {
            $formattedRecord = [];
            foreach ($record as $key => $value) {
                if (trim($key) !== '') {
                    $formattedRecord[trim($key)] = trim($value);
                }
            }
            if (!empty($formattedRecord)) {
                $formattedRecords[] = $formattedRecord;
            }
        }
        return $formattedRecords;
    }

    public function showTest(Request $request)
    {
        // セッションからCSVデータを取得
        $records = Session::get('csv_data', []);

        // ログにセッションデータを記録
        Log::info('CSV data retrieved in showTest method', [
            'data' => $records,
            'session_id' => Session::getId(),
        ]);

        // デバッグメッセージを取得
        $debugMessage = Session::get('debug_message', 'No debug message');

        return view('test', ['records' => $records, 'debugMessage' => $debugMessage]);
    }
}