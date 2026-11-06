@extends('layouts.admin')
@section('title', 'Product details')
@section('content')
<x-page-heading :title="$product->name" :description="$product->category->name . ' / ' . $product->subcategory->name">
    <a class="button button-secondary" href="{{ route('admin.products.index') }}"><x-icon name="arrow-left" :size="17" /> Products</a>
    <a class="button button-primary" href="{{ route('admin.products.edit', $product) }}"><x-icon name="edit" :size="17" /> Edit product</a>
</x-page-heading>
<section class="panel admin-product-detail">
    <div class="image-preview"><img src="{{ $product->imageUrl() }}" alt="{{ $product->name }}" width="640" height="480"></div>
    <div class="detail-copy">
        <span class="status-badge {{ $product->is_active ? 'status-active' : 'status-draft' }}">{{ $product->is_active ? 'Active' : 'Draft' }}</span>
        <h2>Product information</h2>
        <p class="long-text">{{ $product->description }}</p>
        <dl class="detail-facts">
            <div><dt>New price</dt><dd>{{ \App\Support\Money::display($product->new_price) }}</dd></div>
            <div><dt>Old price</dt><dd>{{ $product->old_price !== null ? \App\Support\Money::display($product->old_price) : '—' }}</dd></div>
            <div><dt>URL slug</dt><dd><code>{{ $product->slug }}</code></dd></div>
        </dl>
        @if($product->is_active)<a class="button button-secondary" href="{{ route('shop.products.show', $product->slug) }}">View public page <x-icon name="external" :size="16" /></a>@endif
    </div>
</section>
@endsection
