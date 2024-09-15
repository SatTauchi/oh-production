<?php

namespace App\Http\Controllers;

use App\Models\FishPrice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DataAnalysisController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view('analysis');
    }

    public function getAnalysisData(Request $request)
    {
        $fish = $request->input('fish');
        $user_id = Auth::id();

        $query = FishPrice::where('user_id', $user_id)
                            ->orderBy('date', 'asc');

        if ($fish && $fish !== 'all') {
            $query->where('fish', $fish);
        }

        $data = $query->get(['date', 'price', 'fish']);

        return response()->json($data);
    }

    public function getFishTypes()
    {
        $user_id = Auth::id();
        $fishTypes = FishPrice::where('user_id', $user_id)
                                ->distinct()
                                ->pluck('fish');
        return response()->json($fishTypes);
    }
}