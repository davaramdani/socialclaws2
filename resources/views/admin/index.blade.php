@extends('layouts.app')
@php
    $labels = ['services' => 'Layanan', 'gallery' => 'Galeri', 'bookings' => 'Booking', 'reviews' => 'Ulasan', 'users' => 'Pelanggan'];
    $label = $labels[$resource];
    $isCatalog = in_array($resource, ['services', 'gallery']);
    $statuses = ['pending' => 'Menunggu konfirmasi', 'confirmed' => 'Dikonfirmasi', 'completed' => 'Selesai', 'cancelled' => 'Dibatalkan'];
@endphp
@section('title', 'Kelola '.$label)
@section('content')
<section class="container section">
    <div class="page-intro">
        <p class="eyebrow">ADMIN / SOCIALCLAWS</p>
        <h1>Kelola <em>{{ strtolower($label) }}.</em></h1>
        <p class="muted">{{ $isCatalog ? 'Tambah atau ubah foto, deskripsi, harga, dan durasi.' : 'Periksa dan perbarui data melalui daftar di bawah.' }}</p>
    </div>
    <nav class="admin-nav" aria-label="Navigasi administrator">
        <a class="pill" href="{{ route('admin.dashboard') }}">Ringkasan</a>
        @foreach($labels as $key => $name)
        <a class="pill {{ $resource === $key ? 'active' : '' }}" href="{{ route('admin.index', $key) }}" @if($resource === $key) aria-current="page" @endif>{{ $name }}</a>
        @endforeach
    </nav>
    @if($isCatalog || ($editing && in_array($resource, ['users', 'bookings'])))
    <section class="panel" id="editor" aria-labelledby="editor-title">
        <div class="section-heading"><h2 id="editor-title">{{ $editing ? 'Edit ' : 'Tambah ' }}{{ strtolower($label) }}</h2>@if($editing)<a href="{{ route('admin.index', $resource) }}" class="text-link">Batalkan edit ×</a>@endif</div>
        <form method="POST" action="{{ $editing ? route('admin.update', ['resource' => $resource, 'id' => $editing->id]) : route('admin.store', $resource) }}" enctype="multipart/form-data">
            @csrf
            @if($editing) @method('PUT') @endif
            <div class="form-grid">
                @if($isCatalog)
                <div class="field"><label for="name">Nama {{ strtolower($label) }}</label><input class="input" id="name" name="name" maxlength="30" required value="{{ old('name', $editing->name ?? '') }}"></div>
                <div class="field"><label for="price">Harga (Rp)</label><input class="input" id="price" name="price" type="number" min="0" max="100000000" step="1" required value="{{ old('price', isset($editing) ? (int) $editing->price : '') }}"></div>
                <div class="field"><label for="category">Kategori</label><input class="input" id="category" name="category" maxlength="30" required value="{{ old('category', $editing->category ?? '') }}" placeholder="Contoh: Nail Art"></div>
                <div class="field"><label for="duration_minutes">Durasi (menit)</label><input class="input" id="duration_minutes" name="duration_minutes" required type="number" min="15" max="480" step="5" value="{{ old('duration_minutes', $editing->duration_minutes ?? 60) }}"></div>
                <div class="field"><label for="description">Deskripsi</label><textarea class="input" id="description" name="description" rows="4" required>{{ old('description', $editing->description ?? '') }}</textarea></div>
                <div class="field"><label for="image">Foto {{ $editing ? 'pengganti (opsional)' : '(opsional)' }}</label><input class="input" id="image" type="file" name="image" accept="image/jpeg,image/png,image/webp"><small class="muted">JPG, PNG, atau WebP, maksimal 4 MB.</small>@if($editing && $editing->image)<img src="{{ $editing->image_url }}" alt="Foto {{ $editing->name }} saat ini" width="90" height="90" style="object-fit:cover;margin-top:12px;border-radius:6px">@endif</div>
                @elseif($resource === 'users')
                <div class="field"><label for="full_name">Nama lengkap</label><input class="input" id="full_name" name="full_name" maxlength="30" required value="{{ old('full_name', $editing->full_name) }}"></div>
                <div class="field"><label for="email">Email</label><input class="input" id="email" name="email" type="email" maxlength="30" required value="{{ old('email', $editing->email) }}"></div>
                <div class="field"><label for="address">Alamat</label><textarea class="input" id="address" name="address" rows="3" required>{{ old('address', $editing->address) }}</textarea></div>
                <div class="field"><label for="password">Password baru (opsional)</label><input class="input" id="password" name="password" type="password" minlength="8" autocomplete="new-password"><small class="muted">Kosongkan untuk mempertahankan password saat ini.</small></div>
                @elseif($resource === 'bookings')
                <div class="field"><label for="status">Status booking {{ $editing->reference }}</label><select class="input" id="status" name="status" required>@foreach($statuses as $value => $text)<option value="{{ $value }}" @selected(old('status', $editing->status) === $value)>{{ $text }}</option>@endforeach</select></div>
                <div class="field"><label>Pelanggan</label><p>{{ $editing->customer_name }}<br><span class="muted">{{ $editing->phone }}</span></p></div>
                <div class="field"><label>Alamat</label><p class="muted">{{ $editing->customer_address }}</p></div>
                <div class="field"><label>Detail booking</label><p class="muted" style="white-space:pre-line">{{ $editing->booking_details }}</p></div>
                @endif
            </div>
            <button class="button" type="submit">{{ $editing ? 'Simpan perubahan' : 'Tambah '.strtolower($label) }} <span aria-hidden="true">↗</span></button>
        </form>
    </section>
    @endif
    <div class="section-heading"><h2>{{ $label }} studio</h2><span class="muted">{{ $records->total() }} data</span></div>
    <div class="panel table-wrap">
        <table class="data-table">
            <thead><tr>
                @if($isCatalog)<th scope="col">Koleksi</th><th scope="col">Kategori</th><th scope="col">Harga</th><th scope="col">Durasi</th>
                @elseif($resource === 'users')<th scope="col">Nama</th><th scope="col">Email</th><th scope="col">Alamat</th>
                @elseif($resource === 'bookings')<th scope="col">Booking / pelanggan</th><th scope="col">Jadwal</th><th scope="col">Total</th><th scope="col">Status</th>
                @else<th scope="col">Pelanggan</th><th scope="col">Rating</th><th scope="col">Ulasan</th><th scope="col">Tanggal</th>@endif
                <th scope="col">Aksi</th>
            </tr></thead>
            <tbody>
                @forelse($records as $record)
                <tr>
                    @if($isCatalog)
                    <td><div style="display:flex;align-items:center;gap:14px"><img src="{{ $record->image_url }}" alt="" width="58" height="66" style="object-fit:cover;border-radius:5px"><div><strong>{{ $record->name }}</strong><br><small class="muted">{{ \Illuminate\Support\Str::limit($record->description, 65) }}</small></div></div></td>
                    <td>{{ $record->category ?: '—' }}</td><td>Rp {{ number_format($record->price, 0, ',', '.') }}</td><td>{{ $record->duration_minutes }} menit</td>
                    @elseif($resource === 'users')
                    <td>{{ $record->full_name }}</td><td>{{ $record->email }}</td><td>{{ \Illuminate\Support\Str::limit($record->address, 80) }}</td>
                    @elseif($resource === 'bookings')
                    <td><strong>{{ $record->reference }}</strong><br>{{ $record->customer_name }}<br><small class="muted">{{ $record->phone }}</small></td>
                    <td>{{ $record->appointment_date ? \Illuminate\Support\Carbon::parse($record->appointment_date)->format('d M Y') : 'Belum dijadwalkan' }}<br><small class="muted">{{ $record->appointment_time ? substr((string) $record->appointment_time, 0, 5).' WIB' : '' }}</small></td>
                    <td>Rp {{ number_format($record->total_price, 0, ',', '.') }}</td><td><span class="pill">{{ $statuses[$record->status] ?? $record->status }}</span></td>
                    @else
                    <td>{{ $record->user_name }}</td><td><span aria-label="{{ $record->rating }} dari 5 bintang">{{ str_repeat('★', max(0, min(5, (int) $record->rating))) }}</span></td><td>{{ $record->review_text }}</td><td>{{ $record->created_at ? \Illuminate\Support\Carbon::parse($record->created_at)->format('d M Y') : '—' }}</td>
                    @endif
                    <td><div style="display:flex;align-items:center;gap:14px">
                        @if($resource !== 'reviews')<a class="text-link" href="{{ route('admin.index', ['resource' => $resource, 'edit' => $record->id]) }}#editor">Edit</a>@endif
                        <form method="POST" action="{{ route('admin.destroy', ['resource' => $resource, 'id' => $record->id]) }}" data-confirm="Hapus data ini? Tindakan ini tidak dapat dibatalkan.">@csrf @method('DELETE')<button type="submit" class="text-link" aria-label="Hapus {{ $record->name ?? $record->full_name ?? $record->customer_name ?? $record->user_name }}">Hapus</button></form>
                    </div></td>
                </tr>
                @empty
                <tr><td colspan="6"><div class="empty-state"><h3>Belum ada {{ strtolower($label) }}.</h3><p>{{ $isCatalog ? 'Tambahkan koleksi pertama melalui formulir di atas.' : 'Data akan tampil di sini ketika sudah tersedia.' }}</p></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $records->links() }}
</section>
@endsection
