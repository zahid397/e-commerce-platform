@extends('layouts.admin')
@section('title', 'Dashboard')
@section('content')
<x-page-heading title="Admin dashboard" description="Manage your catalogue and keep track of new orders." eyebrow="STORE OVERVIEW">
    <a href="{{ route('shop.home') }}" class="button button-secondary">View storefront <x-icon name="external" :size="17" /></a>
    <a href="{{ route('admin.products.create') }}" class="button button-primary"><x-icon name="plus" :size="18" /> New product</a>
</x-page-heading>

<div class="metrics">
    @foreach([
        ['categories', 'folder', 'Total categories', 'mint', 'admin.categories.index'],
        ['subcategories', 'layers', 'Total subcategories', 'lavender', 'admin.subcategories.index'],
        ['products', 'box', 'Total products', 'blue', 'admin.products.index'],
        ['active', 'tag', 'Active listings', 'green', 'admin.products.index'],
    ] as [$key, $icon, $label, $tone, $route])
        <a class="metric metric-{{ $tone }}" href="{{ route($route, $key === 'active' ? ['state' => 'active'] : []) }}">
            <span class="metric-top"><x-icon :name="$icon" :size="25" /><x-icon name="external" :size="15" /></span>
            <span class="metric-label">{{ $label }}</span>
            <strong>{{ number_format($counts[$key]) }}</strong>
        </a>
    @endforeach
</div>

<div class="dashboard-grid">
    <section class="panel">
        <header class="panel-heading"><div><h2>Recent products</h2><p>Latest additions to your catalogue.</p></div><a class="text-link" href="{{ route('admin.products.index') }}">View all <x-icon name="arrow-right" :size="15" /></a></header>
        @if($recentProducts->isEmpty())
            <x-empty-state title="No products yet" description="Create a category and subcategory, then add your first product." />
        @else
            <x-admin-product-table :products="$recentProducts" :actions="false" />
        @endif
    </section>
    <div class="dashboard-side">
        <section class="panel quick-panel">
            <header class="panel-heading"><div><h2><x-icon name="bolt" :size="19" /> Quick actions</h2><p>Common catalogue tasks.</p></div></header>
            <div class="quick-list">
                <a class="button button-primary" href="{{ route('admin.products.create') }}"><x-icon name="plus" :size="18" /> Add new product</a>
                <a class="button button-secondary" href="{{ route('admin.categories.create') }}"><x-icon name="folder" :size="18" /> Add category</a>
                <a class="button button-secondary" href="{{ route('admin.subcategories.create') }}"><x-icon name="layers" :size="18" /> Add subcategory</a>
                <a class="button button-secondary" href="{{ route('admin.products.index') }}"><x-icon name="box" :size="18" /> Manage products</a>
            </div>
        </section>
        <section class="panel order-queue">
            <span class="section-icon"><x-icon name="orders" :size="22" /></span>
            <div><h2>Order queue</h2><p>{{ $pendingOrders }} pending {{ \Illuminate\Support\Str::plural('order', $pendingOrders) }}</p></div>
            <a class="icon-button" href="{{ route('admin.orders.index', ['status' => 'pending']) }}" aria-label="Review pending orders"><x-icon name="arrow-right" :size="19" /></a>
        </section>
    </div>
</div>
@endsection
