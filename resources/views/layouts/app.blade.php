<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#efebf4">
    <meta name="description" content="Koleksi nail art dan press-on SocialClaws. Lihat desain, harga layanan, dan ajukan jadwal booking.">
    <title>@yield('title', 'Nail art & press-on studio') — SocialClaws</title>
    <link rel="icon" href="{{ asset('images/Logo.png') }}" type="image/png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;450;500;550;600;650;700;750;800&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}?v=3">
    <link rel="stylesheet" href="{{ asset('css/motion.css') }}?v=1">
    <script src="{{ asset('js/app.js') }}?v=3" defer></script>
    @stack('head')
</head>
<body @class(['home-page' => request()->routeIs('home')])>
    <a class="skip-link" href="#main">Lewati ke konten</a>
    <header class="site-header">
        <div class="container header-inner">
            <a href="{{ route('home') }}" class="wordmark" aria-label="SocialClaws beranda"><span class="brand-symbol" aria-hidden="true"><i></i><i></i><i></i></span><span>socialclaws<small>NAIL STUDIO</small></span></a>
            <nav class="desktop-nav" aria-label="Navigasi utama">
                <a href="{{ route('services') }}" @class(['active' => request()->routeIs('services')])>Layanan</a>
                <a href="{{ route('gallery') }}" @class(['active' => request()->routeIs('gallery')])>Galeri</a>
                <a href="{{ route('reviews') }}" @class(['active' => request()->routeIs('reviews')])>Ulasan</a>
            </nav>
            <div class="header-actions">
                <a class="cart-link" href="{{ route('cart.index') }}" aria-label="Keranjang"><x-icon name="bag" width="18" height="18"/><span>Keranjang</span><span class="cart-count">{{ $cartCount ?? 0 }}</span></a>
                @if(auth('admin')->check())
                    <a class="account-link" href="{{ route('admin.dashboard') }}">Admin</a>
                    <form method="POST" action="{{ route('admin.logout') }}" class="logout-form">@csrf<button class="text-button" type="submit">Keluar</button></form>
                @else
                    @auth
                        <a class="account-link" href="{{ route('bookings.index') }}" aria-label="Booking saya"><x-icon name="user" width="18" height="18"/><span>{{ Str::words(auth()->user()->full_name, 1, '') }}</span></a>
                        <form method="POST" action="{{ route('logout') }}" class="logout-form">@csrf<button class="text-button" type="submit">Keluar</button></form>
                    @else
                        <a class="account-link" href="{{ route('login') }}" aria-label="Masuk">Masuk <x-icon name="arrow-right" width="16" height="16"/></a>
                    @endauth
                @endif
                <button class="icon-button mobile-toggle" type="button" aria-label="Buka menu" aria-controls="mobile-nav" aria-expanded="false" data-menu-toggle><x-icon name="menu"/></button>
            </div>
        </div>
        <nav id="mobile-nav" class="mobile-nav" aria-label="Navigasi seluler" hidden>
            <a href="{{ route('home') }}">Beranda</a><a href="{{ route('services') }}">Layanan &amp; harga</a><a href="{{ route('gallery') }}">Galeri</a><a href="{{ route('reviews') }}">Ulasan</a><a href="{{ route('bookings.index') }}">Booking saya</a>
            @if(auth('admin')->check())<a href="{{ route('admin.dashboard') }}">Admin</a><form action="{{ route('admin.logout') }}" method="POST">@csrf<button class="text-button" type="submit">Keluar admin</button></form>@else @auth<form action="{{ route('logout') }}" method="POST">@csrf<button class="text-button" type="submit">Keluar dari akun</button></form>@endauth @endif
        </nav>
    </header>
    <main id="main">
        @if(session('success'))<div class="container flash-wrap"><div class="flash flash-success" role="status"><x-icon name="check"/>{{ session('success') }}</div></div>@endif
        @if(session('info'))<div class="container flash-wrap"><div class="flash" role="status">{{ session('info') }}</div></div>@endif
        @if(session('error'))<div class="container flash-wrap"><div class="flash flash-error" role="alert">{{ session('error') }}</div></div>@endif
        @if($errors->any())<div class="container flash-wrap"><div class="flash flash-error" role="alert"><strong>Periksa kembali isianmu.</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div></div>@endif
        @yield('content')
    </main>
    <footer class="site-footer">
        <div class="container footer-contact"><div><p>Punya referensi desain?</p><h2>Kirim ke kami.</h2></div><a href="https://wa.me/{{ config('studio.whatsapp_number', '6281211729515') }}" class="button button-lime" target="_blank" rel="noopener">Tanya lewat WhatsApp <x-icon name="arrow-right"/></a></div>
        <div class="container footer-main"><a class="footer-brand" href="{{ route('home') }}">socialclaws.</a><nav class="footer-links" aria-label="Navigasi footer"><a href="{{ route('services') }}">Layanan &amp; harga</a><a href="{{ route('gallery') }}">Galeri</a><a href="{{ route('reviews') }}">Ulasan</a><a href="{{ route('bookings.index') }}">Booking saya</a></nav></div>
        <div class="container footer-bottom"><span>© {{ date('Y') }} SocialClaws</span><span>Nail art &amp; press-on studio</span><a href="{{ route('admin.login') }}">Akses admin</a></div>
    </footer>
    <div class="toast" role="status" aria-live="polite" hidden data-toast></div>
    @stack('scripts')
</body>
</html>
