@extends('layouts.storefront')
@section('title', 'Saved products')
@section('content')
<x-page-heading title="Saved products" description="Products you have saved in this browser session." eyebrow="YOUR SHORTLIST">
    <a class="button button-secondary" href="{{ route('shop.index') }}">Continue browsing <x-icon name="arrow-right" :size="17" /></a>
</x-page-heading>
@if($products->isEmpty())
    <div class="panel"><x-empty-state icon="heart" title="No saved products" description="Use the heart on any product card to save it for later."><a class="button button-primary" href="{{ route('shop.index') }}">Browse products</a></x-empty-state></div>
@else
    <div class="product-grid">@foreach($products as $product)<x-product-card :product="$product" />@endforeach</div>
    <x-pagination :paginator="$products" />
@endif
@endsection
