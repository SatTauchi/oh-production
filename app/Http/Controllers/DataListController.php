<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DataListController extends Controller
{
    public function index()
    {
        return view('list');
    }

    public function edit($id)
    {
        // データ更新ページの処理をここに実装
    }
}