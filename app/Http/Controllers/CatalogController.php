<?php

namespace App\Http\Controllers;

use App\Models\Gallery;
use App\Models\Review;
use App\Models\Service;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function home()
    {
        return view('home', [
            'services' => Service::orderBy('id')->take(3)->get(),
            'gallery' => Gallery::orderByDesc('id')->take(4)->get(),
            'reviews' => Review::orderByDesc('id')->take(3)->get(),
            'reviewCount' => Review::count(),
            'averageRating' => round((float) Review::avg('rating'), 1),
        ]);
    }

    public function services(Request $request)
    {
        return view('services', [
            'services' => $this->catalogQuery(Service::class, $request)->paginate(9)->withQueryString(),
            'categories' => Service::distinct()->orderBy('category')->pluck('category'),
        ]);
    }

    public function gallery(Request $request)
    {
        return view('gallery', [
            'gallery' => $this->catalogQuery(Gallery::class, $request)->paginate(9)->withQueryString(),
            'categories' => Gallery::distinct()->orderBy('category')->pluck('category'),
        ]);
    }

    public function reviews(Request $request)
    {
        return view('reviews', [
            'reviews' => Review::orderByDesc('id')->paginate(9),
            'reviewCount' => Review::count(),
            'averageRating' => round((float) Review::avg('rating'), 1),
            'hasReviewed' => $request->user() ? Review::where('user_id', $request->user()->id)->exists() : false,
        ]);
    }

    private function catalogQuery(string $model, Request $request)
    {
        $filters = $request->validate(['q' => ['nullable', 'string', 'max:100'], 'category' => ['nullable', 'string', 'max:30']]);
        return $model::query()
            ->when($filters['q'] ?? null, fn ($query, $search) => $query->where(function ($query) use ($search) {
                $query->where('name', 'like', '%'.$search.'%')->orWhere('description', 'like', '%'.$search.'%');
            }))
            ->when(($filters['category'] ?? 'all') !== 'all' && ! empty($filters['category']), fn ($query) => $query->where('category', $filters['category']))
            ->orderBy('id');
    }
}
