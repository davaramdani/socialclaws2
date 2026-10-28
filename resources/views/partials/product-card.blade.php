@php($type = $type ?? 'service')
<article class="product-card">
    <div class="product-image">
        <img src="{{ $product->image_url }}" alt="{{ $product->name }}" loading="lazy" width="600" height="700">
        @if($type === 'gallery')<button type="button" class="product-preview icon-button" aria-label="Lihat detail {{ $product->name }}" data-gallery-open data-image="{{ $product->image_url }}" data-title="{{ $product->name }}" data-description="{{ $product->description }}"><x-icon name="search"/></button>@endif
    </div>
    <div class="product-heading"><h3>{{ $product->name }}</h3><span class="product-price">Rp {{ number_format($product->price, 0, ',', '.') }}</span></div>
    <p class="product-description">{{ $product->description }}</p>
    @if($product->duration_minutes)<div class="product-meta"><span>{{ $product->duration_minutes }} menit</span><span>{{ $product->category }}</span></div>@endif
    <div class="product-actions"><a class="text-link" href="{{ route('checkout', ['direct_checkout' => 'true', 'item_id' => $product->id, 'item_type' => $type]) }}">Pilih {{ $type === 'gallery' ? 'desain' : 'layanan' }} <x-icon name="arrow-right" width="17" height="17"/></a><form method="POST" action="{{ route('cart.store') }}">@csrf<input type="hidden" name="item_id" value="{{ $product->id }}"><input type="hidden" name="item_type" value="{{ $type }}"><input type="hidden" name="quantity" value="1"><button class="icon-button" type="submit" aria-label="Tambah {{ $product->name }} ke keranjang"><x-icon name="bag" width="18" height="18"/></button></form></div>
</article>
