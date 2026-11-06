@extends('layouts.storefront')
@section('title', 'Your cart')
@section('content')
<x-page-heading title="Your cart" description="Review quantities before placing an order." eyebrow="SHOPPING CART">
    <a class="button button-secondary" href="{{ route('shop.index') }}"><x-icon name="arrow-left" :size="17" /> Continue shopping</a>
</x-page-heading>
@if(! $quote['lines'] && ! $quote['missing'])
    <div class="panel"><x-empty-state icon="cart" title="Your cart is empty" description="Explore the catalogue and add a product to get started."><a class="button button-primary" href="{{ route('shop.index') }}">Explore products</a></x-empty-state></div>
@else
    <div class="checkout-grid">
        <section class="panel">
            <header class="panel-heading"><div><h2>Cart items</h2><p>Update or remove items below.</p></div></header>
            <ul class="cart-lines">
                @foreach($quote['lines'] as $line)
                    @php($product = $line['product'])
                    <li class="cart-line">
                        <a href="{{ route('shop.products.show', $product->slug) }}" class="cart-product"><img src="{{ $product->imageUrl() }}" alt="" width="72" height="72"><span><strong>{{ $product->name }}</strong><small>{{ \App\Support\Money::display($product->new_price) }} each</small></span></a>
                        <form method="post" action="{{ route('cart.update', $product) }}" class="cart-quantity">
                            @csrf
                            @method('PATCH')
                            <label class="sr-only" for="quantity-{{ $product->id }}">Quantity for {{ $product->name }}</label>
                            <input id="quantity-{{ $product->id }}" class="input" name="quantity" type="number" min="1" max="99" step="1" value="{{ $line['quantity'] }}" required>
                            <button type="submit" class="button button-secondary button-sm" aria-label="Update quantity for {{ $product->name }}">Update</button>
                        </form>
                        <strong class="nowrap cart-subtotal">{{ \App\Support\Money::display($line['subtotal']) }}</strong>
                        <form method="post" action="{{ route('cart.destroy', $product->id) }}">
                            @csrf
                            @method('DELETE')
                            <button class="icon-button danger-button" type="submit" aria-label="Remove {{ $product->name }}"><x-icon name="trash" :size="18" /></button>
                        </form>
                    </li>
                @endforeach
                @foreach($quote['missing'] as $id)
                    <li class="cart-line unavailable-line"><span>This item is no longer available.</span><form method="post" action="{{ route('cart.destroy', $id) }}">@csrf @method('DELETE')<button class="button button-secondary button-sm" type="submit">Remove item</button></form></li>
                @endforeach
            </ul>
        </section>
        <x-cart-summary :quote="$quote" />
    </div>
@endif
@endsection
