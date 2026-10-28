@extends('layouts.app')
@section('title', 'Dashboard studio')
@section('content')
<section class="container section">
    <div class="page-intro">
        <p class="eyebrow">ADMIN / SOCIALCLAWS</p>
        <h1>Ringkasan studio.</h1>
        <p class="muted">Lihat booking terbaru dan kelola data studio.</p>
    </div>
    <nav class="admin-nav" aria-label="Navigasi administrator">
        <a class="pill active" href="{{ route('admin.dashboard') }}" aria-current="page">Ringkasan</a>
        @foreach(['services' => 'Layanan', 'gallery' => 'Galeri', 'bookings' => 'Booking', 'reviews' => 'Ulasan', 'users' => 'Pelanggan'] as $key => $label)
        <a class="pill" href="{{ route('admin.index', $key) }}">{{ $label }}</a>
        @endforeach
    </nav>
    <div class="stats-grid">
        @foreach(['bookings' => 'Total booking', 'pending' => 'Menunggu konfirmasi', 'revenue' => 'Nilai booking selesai', 'users' => 'Pelanggan', 'services' => 'Layanan', 'gallery' => 'Desain galeri'] as $key => $label)
        <div class="stat-card">
            <p class="muted">{{ $label }}</p>
            <strong>{{ $key === 'revenue' ? 'Rp '.number_format($stats[$key] ?? 0, 0, ',', '.') : number_format($stats[$key] ?? 0, 0, ',', '.') }}</strong>
        </div>
        @endforeach
    </div>
    <div class="section-heading">
        <div><p class="eyebrow">BOOKING</p><h2>Booking terbaru</h2></div>
        <a class="text-link" href="{{ route('admin.index', 'bookings') }}">Semua booking <span aria-hidden="true">↗</span></a>
    </div>
    <div class="panel table-wrap">
        <table class="data-table">
            <thead><tr><th scope="col">Booking</th><th scope="col">Pelanggan</th><th scope="col">Jadwal</th><th scope="col">Total</th><th scope="col">Status</th><th scope="col">Aksi</th></tr></thead>
            <tbody>
                @forelse($recentBookings as $booking)
                <tr>
                    <td>{{ $booking->reference }}</td>
                    <td>{{ $booking->customer_name }}</td>
                    <td>{{ $booking->appointment_date ? \Illuminate\Support\Carbon::parse($booking->appointment_date)->format('d M Y') : 'Belum dijadwalkan' }}<br><small class="muted">{{ $booking->appointment_time ? substr((string) $booking->appointment_time, 0, 5) . ' WIB' : '' }}</small></td>
                    <td>Rp {{ number_format($booking->total_price, 0, ',', '.') }}</td>
                    <td><span class="pill">{{ ['pending'=>'Menunggu', 'confirmed'=>'Dikonfirmasi', 'completed'=>'Selesai', 'cancelled'=>'Dibatalkan'][$booking->status] ?? $booking->status }}</span></td>
                    <td><a class="text-link" href="{{ route('admin.index', ['resource' => 'bookings', 'edit' => $booking->id]) }}">Kelola ↗</a></td>
                </tr>
                @empty
                <tr><td colspan="6"><div class="empty-state"><h3>Belum ada booking.</h3><p>Booking pelanggan akan muncul di sini.</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
