@extends('layouts.app')
@section('title', 'Nail art & press-on studio')
@section('content')
<section class="lookbook" aria-labelledby="studio-title">
    <div class="lookbook-top container">
        <p>Nail art &amp; press-on studio</p>
        <a href="{{ route('gallery') }}">Lihat karya kami <x-icon name="arrow-right" width="18" height="18"/></a>
    </div>
    <div class="container masthead-wrap"><h1 id="studio-title" class="studio-masthead">socialclaws<span class="masthead-dot">.</span></h1></div>
    <div class="lookbook-photos container">
        <a class="lookbook-photo lookbook-photo-sage" href="{{ route('gallery') }}">
            <img src="{{ asset('uploads/Gambar3.jpg') }}" alt="Set press-on hijau sage dengan detail silver dari SocialClaws" width="650" height="650" fetchpriority="high">
            <div class="photo-label"><span>Sage &amp; silver</span><span>01</span></div>
        </a>
        <a class="lookbook-photo lookbook-photo-chrome" href="{{ route('gallery') }}">
            <img src="{{ asset('uploads/Gambar1.jpg') }}" alt="Nail art hitam dan burgundy chrome dengan detail timbul" width="900" height="1100" fetchpriority="high">
            <div class="photo-label"><span>Burgundy chrome</span><span>02</span></div>
        </a>
        <a class="lookbook-photo lookbook-photo-gold" href="{{ route('gallery') }}">
            <img src="{{ asset('images/Gambar2.jpg') }}" alt="Set kuku smoky brown dengan aksen emas dan kristal" width="650" height="700" fetchpriority="high">
            <div class="photo-label"><span>Smoky gold</span><span>03</span></div>
        </a>
    </div>
    <div class="lookbook-bottom container">
        <h2>Nail art, gel polish<br>&amp; press-on nails.</h2>
        <p>Lihat koleksi nail art, cek harga layanan,<br class="desktop-break"> lalu pilih jadwalmu.</p>
        <a class="button button-lime" href="{{ route('services') }}">Lihat layanan <x-icon name="arrow-right"/></a>
    </div>
</section>
<section class="menu-section section" aria-labelledby="treatment-title">
    <div class="container">
        <div class="section-heading"><h2 id="treatment-title">Perawatan &amp; harga<span class="heading-dot">.</span></h2><a class="text-link" href="{{ route('services') }}">Semua layanan <x-icon name="arrow-right" width="18" height="18"/></a></div>
        <div class="treatment-list">
            @forelse($services as $service)
                <a class="treatment-row" href="{{ route('checkout', ['direct_checkout' => 'true', 'item_id' => $service->id, 'item_type' => 'service']) }}">
                    <span class="treatment-number">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                    <img src="{{ $service->image_url }}" alt="" width="100" height="110" loading="lazy">
                    <div class="treatment-description"><h3>{{ $service->name }}</h3><p>{{ $service->description }}</p></div>
                    <span class="treatment-duration">{{ $service->duration_minutes }} menit</span>
                    <strong class="treatment-price">Rp {{ number_format($service->price, 0, ',', '.') }}</strong>
                    <span class="row-arrow"><x-icon name="arrow-right"/></span>
                </a>
            @empty
                <div class="empty-state"><h3>Belum ada layanan.</h3><p>Hubungi kami untuk menanyakan layanan yang tersedia.</p><a class="text-link" href="https://wa.me/{{ config('studio.whatsapp_number') }}">Tanya melalui WhatsApp <x-icon name="arrow-right"/></a></div>
            @endforelse
        </div>
    </div>
</section>
<section class="collection-section section" aria-labelledby="collection-title">
    <div class="container">
        <div class="section-heading"><div><h2 id="collection-title">Dari galeri<span class="heading-dot">.</span></h2><p>Temukan warna dan bentuk untuk referensi nail art-mu.</p></div><a class="text-link" href="{{ route('gallery') }}">Buka galeri <x-icon name="arrow-right" width="18" height="18"/></a></div>
        <div class="collection-grid">
            @forelse($gallery as $look)
                <a class="collection-item" href="{{ route('gallery', ['q' => $look->name]) }}">
                    <div class="collection-image"><img src="{{ $look->image_url }}" alt="{{ $look->name }}" width="550" height="650" loading="lazy"><span class="collection-open" aria-hidden="true"><x-icon name="arrow-right"/></span></div>
                    <div class="collection-caption"><h3>{{ $look->name }}</h3><span>Rp {{ number_format($look->price, 0, ',', '.') }}</span></div>
                </a>
            @empty
                <div class="empty-state"><h3>Galeri belum diisi.</h3><p>Koleksi desain akan tampil di sini.</p></div>
            @endforelse
        </div>
    </div>
</section>
@if($reviews->isNotEmpty())
<section class="customer-section section" aria-labelledby="customer-title">
    <div class="container customer-layout">
        <div class="customer-summary"><h2 id="customer-title">Kata mereka<span class="heading-dot">.</span></h2><div class="rating-inline"><strong>{{ number_format($averageRating, 1) }}</strong><span>dari 5<br>{{ $reviewCount }} ulasan pelanggan</span></div><a class="text-link" href="{{ route('reviews') }}">Baca semua ulasan <x-icon name="arrow-right" width="18" height="18"/></a></div>
        <div class="review-grid">@foreach($reviews as $review)@include('partials.review-card')@endforeach</div>
    </div>
</section>
@endif
@endsection
