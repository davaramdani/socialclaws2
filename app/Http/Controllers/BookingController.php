<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Gallery;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        return view('bookings.index', ['bookings' => $request->user()->bookings()->orderByDesc('id')->paginate(10)]);
    }

    public function checkout(Request $request)
    {
        $direct = $request->boolean('direct_checkout') || $request->input('checkout_type') === 'direct';
        $items = $this->checkoutItems($request, $direct);
        if ($items->isEmpty()) {
            return redirect()->route('services')->with('info', 'Pilih layanan atau desain terlebih dahulu.');
        }
        return view('checkout', ['items' => $items, 'total' => $items->sum(fn ($item) => $item['price'] * $item['quantity']), 'checkoutType' => $direct ? 'direct' : 'cart']);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'checkout_type' => ['required', Rule::in(['direct', 'cart'])],
            'customer_name' => ['required', 'string', 'max:30'],
            'customer_address' => ['required', 'string', 'max:2000'],
            'phone' => ['bail', 'required', 'string', 'regex:/^[+0-9 ()-]{8,25}$/', function ($attribute, $value, $fail) {
                $digits = strlen(preg_replace('/\D/', '', $value));
                if ($digits < 8 || $digits > 15) { $fail('Masukkan nomor WhatsApp yang valid (8–15 digit).'); }
            }],
            'appointment_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'appointment_time' => ['required', 'date_format:H:i', Rule::in(['09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00', '18:00'])],
        ]);
        $appointment = \Illuminate\Support\Carbon::parse($data['appointment_date'].' '.$data['appointment_time']);
        if ($appointment->isPast()) {
            throw ValidationException::withMessages(['appointment_time' => 'Pilih waktu yang belum berlalu.']);
        }
        $booking = DB::transaction(function () use ($request, $data) {
            User::whereKey($request->user()->id)->lockForUpdate()->firstOrFail();
            $items = $this->checkoutItems($request, $data['checkout_type'] === 'direct', true);
            if ($items->isEmpty()) {
                throw ValidationException::withMessages(['checkout_type' => 'Keranjang kosong. Pilih layanan terlebih dahulu.']);
            }
            $total = $items->sum(fn ($item) => $item['price'] * $item['quantity']);
            if ($total > 2147483647) {
                throw ValidationException::withMessages(['checkout_type' => 'Total pesanan terlalu besar. Kurangi jumlah item atau hubungi studio.']);
            }
            $details = $items->map(fn ($item) => $item['name'].' × '.$item['quantity'].' — Rp '.number_format($item['price'] * $item['quantity'], 0, ',', '.'))->implode("\n");
            $booking = Booking::create([
                'user_id' => $request->user()->id, 'customer_name' => $data['customer_name'],
                'customer_address' => $data['customer_address'], 'phone' => $data['phone'],
                'appointment_date' => $data['appointment_date'], 'appointment_time' => $data['appointment_time'],
                'booking_details' => $details, 'total_price' => $total,
                'status' => 'pending',
            ]);
            if ($data['checkout_type'] === 'cart') {
                $request->user()->cartItems()->delete();
            }
            return $booking;
        }, 3);
        return redirect()->route('booking.success', $booking)->with('success', 'Booking tersimpan. Konfirmasikan jadwalmu melalui WhatsApp.');
    }

    public function success(Request $request, Booking $booking)
    {
        abort_unless($booking->user_id === $request->user()->id, 404);
        $message = "Halo SocialClaws, saya ingin mengonfirmasi booking {$booking->reference}.\n\nNama: {$booking->customer_name}\nTelepon: {$booking->phone}\nAlamat: {$booking->customer_address}\nJadwal: ".($booking->appointment_date?->format('d M Y') ?? '-')." {$booking->appointment_time}\n\n{$booking->booking_details}\n\nTotal: Rp ".number_format($booking->total_price, 0, ',', '.');
        $whatsappUrl = 'https://wa.me/'.preg_replace('/\D/', '', config('studio.whatsapp_number', '6281211729515')).'?text='.rawurlencode($message);
        return view('booking.success', compact('booking', 'whatsappUrl'));
    }

    private function checkoutItems(Request $request, bool $direct, bool $lock = false)
    {
        if ($direct) {
            $data = $request->validate(['item_type' => ['required', Rule::in(['service', 'gallery'])], 'item_id' => ['required', 'integer', 'min:1']]);
            $model = $data['item_type'] === 'service' ? Service::class : Gallery::class;
            $item = $model::when($lock, fn ($query) => $query->lockForUpdate())->findOrFail($data['item_id']);
            return collect([['item_id' => $item->id, 'item_type' => $data['item_type'], 'name' => $item->name, 'price' => $item->price, 'quantity' => 1, 'image' => $item->image_url]]);
        }
        $rows = $request->user()->cartItems()->with([
            'service' => fn ($query) => $query->when($lock, fn ($query) => $query->lockForUpdate()),
            'galleryItem' => fn ($query) => $query->when($lock, fn ($query) => $query->lockForUpdate()),
        ])->when($lock, fn ($query) => $query->lockForUpdate())->get();
        if ($rows->contains(fn ($row) => $row->item === null)) {
            throw ValidationException::withMessages(['checkout_type' => 'Ada layanan yang sudah tidak tersedia. Hapus item tersebut dari keranjang.']);
        }
        return $rows->map(fn ($row) => ['item_id' => $row->item_id, 'item_type' => $row->item_type, 'name' => $row->item->name, 'price' => $row->item->price, 'quantity' => $row->quantity, 'image' => $row->item->image_url]);
    }
}
