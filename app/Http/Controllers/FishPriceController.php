<?php

namespace App\Http\Controllers;

use App\Models\FishPrice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class FishPriceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $fishPrices = FishPrice::where('delete_flg', 0)->get();
        return view('list', compact('fishPrices'));
    }

    public function create()
    {
        return view('input');
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'date' => 'required|date',
            'fish' => 'required|string',
            'place' => 'nullable|string',
            'price' => 'required|integer|min:0',  // price を int に変更
            'selling_price' => 'nullable|integer|min:0',  // selling_price も int に変更
            'quantity_sold' => 'nullable|integer|min:0',
            'remarks' => 'nullable|string|max:200',
            'imgFile' => 'nullable|image|max:2048',
        ]);

        $validatedData['user_id'] = auth()->id();

        if ($request->hasFile('imgFile')) {
            $path = $request->file('imgFile')->store('fish_images', 'public');
            $validatedData['image_path'] = $path;
        }

        FishPrice::create($validatedData);

        flash()->success('データが正常に保存されました。');
        return view('input');
    }

    public function getData(Request $request)
    {
        $fish = $request->input('fish');
        $user_id = Auth::id();

        $query = FishPrice::where('user_id', $user_id)
        ->where('delete_flg', 0);  // delete_flg が 0 のデータのみを取得

        if ($fish && $fish !== 'all') {
            $query->where('fish', $fish);
        }

        $data = $query->get()->map(function ($item) {
            return [
                'id' => $item->id,
                'date' => Carbon::parse($item->date)->format('Y-m-d'),
                'fish' => $item->fish,
                'place' => $item->place,
                'price' => intval($item->price),  // price を int として扱う
                'selling_price' => $item->selling_price ? intval($item->selling_price) : null,
                'quantity_sold' => $item->quantity_sold ? intval($item->quantity_sold) : null,
                'remarks' => $item->remarks,
                'photo' => $item->image_path ? asset('storage/' . $item->image_path) : null,
            ];
        });

        return response()->json($data);
    }

    public function deleteData(Request $request)
    {
        $id = $request->input('id');
        $user_id = Auth::id();

        $fishPrice = FishPrice::where('id', $id)
                                ->where('user_id', $user_id)
                                ->firstOrFail();

        if ($fishPrice->image_path) {
            Storage::disk('public')->delete($fishPrice->image_path);
        }

        $fishPrice->delete();

        return response()->json(['success' => true]);
    }

    public function edit($id)
    {
        $fishPrice = FishPrice::findOrFail($id);
        $this->authorize('update', $fishPrice);

        $result = [
            'id' => $fishPrice->id,
            'date' => Carbon::parse($fishPrice->date)->format('Y-m-d'),
            'fish' => $fishPrice->fish,
            'place' => $fishPrice->place,
            'price' => intval($fishPrice->price),  // price を int として扱う
            'selling_price' => $fishPrice->selling_price,
            'quantity_sold' => $fishPrice->quantity_sold,
            'remarks' => $fishPrice->remarks,
            'photo' => $fishPrice->image_path ? asset('storage/' . $fishPrice->image_path) : null,
        ];

        return view('data_update', compact('result'));
    }

    public function update(Request $request, $id)
    {
        $fishPrice = FishPrice::findOrFail($id);
        $this->authorize('update', $fishPrice);

        $validatedData = $request->validate([
            'date' => 'required|date',
            'fish' => 'required|string',
            'place' => 'required|string',
            'price' => 'required|integer|min:0',  // price を int に変更
            'selling_price' => 'nullable|integer|min:0',  // selling_price も int に変更
            'quantity_sold' => 'nullable|integer|min:0',
            'remarks' => 'nullable|string|max:200',
            'imgFile' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('imgFile')) {
            if ($fishPrice->image_path) {
                Storage::disk('public')->delete($fishPrice->image_path);
            }
            $path = $request->file('imgFile')->store('fish_images', 'public');
            $validatedData['image_path'] = $path;
        }

        $fishPrice->update($validatedData);

        flash()->success('データが正常に保存されました。');
        return redirect()->route('data.list');
    }

    public function softDelete(Request $request)
    {
        $id = $request->input('id');
        $fishPrice = FishPrice::findOrFail($id);
        
        $this->authorize('delete', $fishPrice);

        $fishPrice->update(['delete_flg' => 1]);

        flash()->success('データが正常に削除されました。');
        
        return response()->json(['success' => true]);
    }
}
