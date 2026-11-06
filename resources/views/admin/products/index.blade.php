@extends('layouts.admin')
@section('title', 'Products')
@section('content')
<x-page-heading title="Products" description="Manage images, prices and storefront availability.">
    <a class="button button-primary" href="{{ route('admin.products.create') }}"><x-icon name="plus" :size="18" /> New product</a>
</x-page-heading>
<x-catalog-filters :categories="$categories" :filters="$filters" :admin="true" />
<section class="panel">
    <header class="panel-heading"><div><h2>Product list</h2><p>{{ $products->total() }} {{ \Illuminate\Support\Str::plural('product', $products->total()) }}</p></div></header>
    @if($products->isEmpty())
        <x-empty-state title="No products found" description="Try a different search or add a product.">
            <a class="button button-secondary" href="{{ route('admin.products.create') }}">Add a product</a>
        </x-empty-state>
    @else
        <x-admin-product-table :products="$products" />
        <x-pagination :paginator="$products" />
    @endif
</section>
@endsection
