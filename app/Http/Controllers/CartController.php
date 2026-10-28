<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Gallery;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CartController extends Controller
{
    public function index(Request $request)
    {
        $items = $request->user()->cartItems()->with(['service', 'galleryItem'])->orderBy('id')->get();
        return view('cart', ['items' => $items, 'total' => $items->sum('subtotal')]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'item_type' => ['required', Rule::in(['service', 'gallery'])],
            'item_id' => ['required', 'integer', 'min:1'],
            'quantity' => ['sometimes', 'integer', 'between:1,20'],
        ]);
        $model = $data['item_type'] === 'service' ? Service::class : Gallery::class;
        $model::findOrFail($data['item_id']);
        DB::transaction(function () use ($request, $data) {
            User::whereKey($request->user()->id)->lockForUpdate()->firstOrFail();
            $query = $request->user()->cartItems()->where('item_type', $data['item_type'])->where('item_id', $data['item_id']);
            $existing = $query->get();
            $quantity = $existing->sum('quantity') + ($data['quantity'] ?? 1);
            if ($quantity > 20) {
                throw ValidationException::withMessages(['quantity' => 'Maksimal 20 item untuk setiap layanan.']);
            }
            $row = $existing->first();
            if ($row) {
                $row->update(['quantity' => $quantity]);
                $query->where('id', '!=', $row->id)->delete();
            } else {
                $request->user()->cartItems()->create([...$data, 'quantity' => $quantity]);
            }
        });
        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Pilihanmu ditambahkan ke keranjang.', 'cart_count' => $request->user()->cartItems()->sum('quantity')]);
        }
        return back()->with('success', 'Pilihanmu ditambahkan ke keranjang.');
    }

    public function update(Request $request, CartItem $cartItem)
    {
        abort_unless($cartItem->user_id === $request->user()->id, 404);
        $cartItem->update($request->validate(['quantity' => ['required', 'integer', 'between:1,20']]));
        return back()->with('success', 'Jumlah item diperbarui.');
    }

    public function destroy(Request $request, CartItem $cartItem)
    {
        abort_unless($cartItem->user_id === $request->user()->id, 404);
        $cartItem->delete();
        return back()->with('success', 'Item dihapus dari keranjang.');
    }
}
