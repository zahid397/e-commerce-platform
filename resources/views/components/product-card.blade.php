@props(['product'])
@php
    $saved = in_array($product->id, session('favorites', []), true);
    $discount = $product->discountPercent();
    $url = route('shop.products.show', $product->slug);
@endphp
<article class="product-card">
    <div class="product-media">
        <a href="{{ $url }}" class="product-image-link" aria-label="View {{ $product->name }}">
            <img src="{{ $product->imageUrl() }}" alt="{{ $product->name }}" width="640" height="480" loading="lazy" decoding="async">
        </a>
        @if($discount > 0)<span class="discount-tag">−{{ $discount }}%</span>@endif
        <form method="post" action="{{ route('favorites.toggle', $product) }}" class="favorite-form">
            @csrf
            <button class="icon-button favorite-button {{ $saved ? 'is-saved' : '' }}" type="submit" aria-pressed="{{ $saved ? 'true' : 'false' }}" aria-label="{{ $saved ? 'Remove saved ' : 'Save ' }}{{ $product->name }}">
                <x-icon name="heart" :size="19" />
            </button>
        </form>
    </div>
    <div class="product-card-body">
        <p class="product-kind">{{ $product->category->name }} <span aria-hidden="true">·</span> {{ $product->subcategory->name }}</p>
        <h3><a href="{{ $url }}">{{ $product->name }}</a></h3>
        <div class="product-price">
            <strong>{{ \App\Support\Money::display($product->new_price) }}</strong>
            @if($product->old_price !== null && \App\Support\Money::minor($product->old_price) > \App\Support\Money::minor($product->new_price))
                <del><span class="sr-only">Old price </span>{{ \App\Support\Money::display($product->old_price) }}</del>
            @endif
        </div>
        <form method="post" action="{{ route('cart.store', $product) }}" class="card-cart" data-busy>
            @csrf
            <input type="hidden" name="quantity" value="1">
            <button class="button button-soft button-wide" type="submit" data-busy-label="Adding…" aria-label="Add {{ $product->name }} to cart"><x-icon name="cart" :size="17" /> Add to cart</button>
        </form>
    </div>
</article>
