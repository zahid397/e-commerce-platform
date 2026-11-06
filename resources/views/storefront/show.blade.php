@extends('layouts.storefront')
@section('title', $product->name)
@section('description', \Illuminate\Support\Str::limit(strip_tags($product->description), 155))
@section('canonical', route('shop.products.show', $product->slug))
@section('content')
<nav aria-label="Breadcrumb" class="breadcrumbs">
    <a href="{{ route('shop.home') }}">Home</a><x-icon name="chevron" :size="13" />
    <a href="{{ route('shop.index', ['category' => $product->category_id]) }}">{{ $product->category->name }}</a><x-icon name="chevron" :size="13" />
    <a href="{{ route('shop.index', ['category' => $product->category_id, 'subcategory' => $product->subcategory_id]) }}">{{ $product->subcategory->name }}</a>
    <x-icon name="chevron" :size="13" /><span aria-current="page">{{ $product->name }}</span>
</nav>
<section class="panel product-detail">
    <div class="detail-media">
        <img src="{{ $product->imageUrl() }}" alt="{{ $product->name }}" width="640" height="480" fetchpriority="high">
        @if($product->discountPercent() > 0)<span class="discount-tag">−{{ $product->discountPercent() }}%</span>@endif
    </div>
    <div class="detail-copy">
        <p class="eyebrow">{{ $product->category->name }} / {{ $product->subcategory->name }}</p>
        <h1>{{ $product->name }}</h1>
        <div class="product-price detail-price">
            <strong>{{ \App\Support\Money::display($product->new_price) }}</strong>
            @if($product->old_price !== null && \App\Support\Money::minor($product->old_price) > \App\Support\Money::minor($product->new_price))
                <del><span class="sr-only">Old price </span>{{ \App\Support\Money::display($product->old_price) }}</del>
            @endif
        </div>
        <p class="long-text">{{ $product->description }}</p>
        <form class="detail-cart" method="post" action="{{ route('cart.store', $product) }}" data-busy>
            @csrf
            <div class="field">
                <label class="field-label" for="quantity">Quantity</label>
                <div class="quantity-control" data-quantity>
                    <button class="icon-button quantity-step" type="button" data-step="-1" aria-label="Decrease quantity"><x-icon name="minus" :size="16" /></button>
                    <input id="quantity" class="input" type="number" name="quantity" min="1" max="99" step="1" required value="{{ \App\Support\OldInput::string('quantity', 1) }}">
                    <button class="icon-button quantity-step" type="button" data-step="1" aria-label="Increase quantity"><x-icon name="plus" :size="16" /></button>
                </div>
            </div>
            <button class="button button-primary" type="submit" data-busy-label="Adding…"><x-icon name="cart" :size="20" /> Add to cart</button>
        </form>
        @php($saved = in_array($product->id, session('favorites', []), true))
        <form method="post" action="{{ route('favorites.toggle', $product) }}">
            @csrf
            <button type="submit" class="button button-secondary" aria-pressed="{{ $saved ? 'true' : 'false' }}"><x-icon name="heart" :size="18" /> {{ $saved ? 'Remove from saved' : 'Save product' }}</button>
        </form>
    </div>
</section>
@if($related->isNotEmpty())
    <section class="shop-section">
        <div class="section-heading"><div><p class="eyebrow">MORE TO EXPLORE</p><h2>In the same subcategory</h2></div></div>
        <div class="product-grid">@foreach($related as $item)<x-product-card :product="$item" />@endforeach</div>
    </section>
@endif
@endsection
