<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReviewController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate(['rating' => ['required', 'integer', 'between:1,5'], 'review_text' => ['required', 'string', 'min:10', 'max:2000']]);
        DB::transaction(function () use ($request, $data) {
            User::whereKey($request->user()->id)->lockForUpdate()->firstOrFail();
            if (Review::where('user_id', $request->user()->id)->exists()) {
                throw ValidationException::withMessages(['review_text' => 'Kamu sudah memberikan ulasan. Terima kasih!']);
            }
            Review::create([...$data, 'user_id' => $request->user()->id, 'user_name' => $request->user()->full_name]);
        });
        return redirect()->route('reviews')->with('success', 'Terima kasih sudah berbagi pengalamanmu.');
    }
}
