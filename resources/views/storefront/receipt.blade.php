@extends('layouts.storefront')
@section('title', 'Order confirmation')
@section('content')
<section class="panel receipt">
    <div class="receipt-heading">
        <span class="receipt-mark"><x-icon name="check" :size="32" /></span>
        <p class="eyebrow">ORDER CONFIRMATION</p>
        <h1>Order placed</h1>
        <p>Your order is saved and available for admin review.</p>
        <p class="receipt-reference">Reference <strong>{{ $order->reference }}</strong></p>
        <x-order-badge :status="$order->status" />
    </div>
    <div class="receipt-body"><x-order-lines :order="$order" /></div>
    <div class="receipt-actions"><a class="button button-primary" href="{{ route('shop.index') }}">Continue shopping <x-icon name="arrow-right" :size="17" /></a></div>
</section>
@endsection
