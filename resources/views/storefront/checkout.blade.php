@extends('layouts.storefront')
@section('title', 'Checkout')
@section('content')
<x-page-heading title="Checkout" description="Enter delivery details and review the items in your order." eyebrow="PLACE AN ORDER">
    <a class="button button-secondary" href="{{ route('cart.index') }}"><x-icon name="arrow-left" :size="17" /> Back to cart</a>
</x-page-heading>
<div class="checkout-grid">
    <section class="panel">
        <header class="panel-heading"><div><h2>Delivery details</h2><p>For a demo, use sample contact information.</p></div><span class="section-icon"><x-icon name="user" :size="22" /></span></header>
        <form class="panel-body form-stack" method="post" action="{{ route('checkout.store') }}" data-busy>
            @csrf
            <input type="hidden" name="checkout_token" value="{{ $token }}">
            <x-field name="name" label="Full name" maxlength="120" autocomplete="name" :required="true" />
            <div class="form-pair">
                <x-field name="email" label="Email address" type="email" maxlength="254" autocomplete="email" :required="true" />
                <x-field name="phone" label="Phone number" type="tel" maxlength="25" autocomplete="tel" :required="true" />
            </div>
            <x-field name="address" label="Delivery address" :multiline="true" rows="3" maxlength="500" autocomplete="street-address" :required="true" />
            <div class="form-note"><x-icon name="shield" :size="19" /> Your order will be saved for admin review. This demo does not collect payment or send an email.</div>
            <div class="form-actions"><button type="submit" class="button button-primary" data-busy-label="Placing order…">Place order <x-icon name="arrow-right" :size="18" /></button></div>
        </form>
    </section>
    <x-cart-summary :quote="$quote" :checkout="true" />
</div>
@endsection
