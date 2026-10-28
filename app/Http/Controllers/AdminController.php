<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\CartItem;
use App\Models\Gallery;
use App\Models\Review;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    private const MODELS = ['services' => Service::class, 'gallery' => Gallery::class, 'users' => User::class, 'reviews' => Review::class, 'bookings' => Booking::class];

    public function dashboard()
    {
        $stats = [
            'services' => Service::count(), 'gallery' => Gallery::count(), 'users' => User::count(),
            'bookings' => Booking::count(), 'pending' => Booking::where('status', 'pending')->count(),
            'revenue' => Booking::where('status', 'completed')->sum('total_price'),
        ];
        return view('admin.dashboard', ['stats' => $stats, 'recentBookings' => Booking::orderByDesc('id')->take(8)->get()]);
    }

    public function index(Request $request, string $resource)
    {
        $model = $this->model($resource);
        $editing = $request->filled('edit') ? $model::findOrFail($request->integer('edit')) : null;
        return view('admin.index', ['resource' => $resource, 'records' => $model::orderByDesc('id')->paginate(15)->withQueryString(), 'editing' => $editing]);
    }

    public function store(Request $request, string $resource)
    {
        abort_unless(in_array($resource, ['services', 'gallery'], true), 405);
        $data = $this->catalogData($request);
        if (! isset($data['image'])) {
            $data['image'] = 'images/nail-art.jpg';
        }
        $model = $this->model($resource);
        $model::create($data);
        return redirect()->route('admin.index', $resource)->with('success', 'Item baru berhasil ditambahkan.');
    }

    public function update(Request $request, string $resource, int $id)
    {
        $model = $this->model($resource);
        $record = $model::findOrFail($id);
        if (in_array($resource, ['services', 'gallery'], true)) {
            $data = $this->catalogData($request);
        } elseif ($resource === 'users') {
            $data = $request->validate([
                'full_name' => ['required', 'string', 'max:30'],
                'email' => ['required', 'email', 'max:30', Rule::unique('users')->ignore($id)],
                'address' => ['required', 'string', 'max:2000'],
                'password' => ['nullable', 'string', 'min:8', 'max:72'],
            ]);
            if (empty($data['password'])) { unset($data['password']); }
            else { $data['password'] = Hash::make($data['password']); }
        } elseif ($resource === 'bookings') {
            $data = $request->validate(['status' => ['required', Rule::in(['pending', 'confirmed', 'completed', 'cancelled'])]]);
        } else {
            abort(405);
        }
        $record->update($data);
        return redirect()->route('admin.index', $resource)->with('success', 'Perubahan berhasil disimpan.');
    }

    public function destroy(string $resource, int $id)
    {
        $model = $this->model($resource);
        $record = $model::findOrFail($id);
        DB::transaction(function () use ($resource, $record) {
            if (in_array($resource, ['services', 'gallery'], true)) {
                CartItem::where('item_type', $resource === 'services' ? 'service' : 'gallery')->where('item_id', $record->id)->delete();
            } elseif ($resource === 'users') {
                CartItem::where('user_id', $record->id)->delete();
                Review::where('user_id', $record->id)->delete();
                Booking::where('user_id', $record->id)->delete();
            }
            $record->delete();
        });
        return redirect()->route('admin.index', $resource)->with('success', 'Data berhasil dihapus.');
    }

    private function model(string $resource): string
    {
        abort_unless(isset(self::MODELS[$resource]), 404);
        return self::MODELS[$resource];
    }

    private function catalogData(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:30'],
            'description' => ['required', 'string', 'max:3000'],
            'price' => ['required', 'integer', 'between:0,100000000'],
            'duration_minutes' => ['required', 'integer', 'between:15,480'],
            'category' => ['required', 'string', 'max:30'],
            'image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:4096'],
        ]);
        unset($data['image']);
        if ($request->hasFile('image')) {
            // UUID + extension keeps the path inside the legacy gallery.image VARCHAR(50).
            $name = \Illuminate\Support\Str::uuid().'.'.$request->file('image')->extension();
            $request->file('image')->move(public_path('uploads'), $name);
            $data['image'] = 'uploads/'.$name;
        }
        return $data;
    }
}
