<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function index(Request $request)
    {
        $comments = Comment::with('user')
            ->whereHas('user', function ($query) use ($request) {
                $query->where('branch_id', $request->user()->branch_id);
            })
            ->latest()
            ->take(5)
            ->get();

        return response()->json($comments);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'content' => 'required|string|max:500',
        ]);

        $comment = $request->user()->comments()->create($validated);

        return redirect()->back()->with('success', 'コメントが投稿されました。');
    }
}