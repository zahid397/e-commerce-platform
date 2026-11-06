@extends('layouts.storefront')
@section('title', $home ? 'Discover everyday tech' : 'Browse products')
@section('content')
@if($home)
    <section class="hero">
        <div class="hero-copy">
            <p class="hero-kicker"><span></span> THE EVERYDAY TECH EDIT</p>
            <h1>Discover top products at <em>great prices.</em></h1>
            <p class="hero-description">Find practical tech for work, travel and everyday life. Browse by category or search for something specific.</p>
            <div class="hero-actions">
                <a class="button button-primary" href="{{ route('shop.index') }}">Explore products <x-icon name="arrow-right" :size="19" /></a>
                <a class="button button-hero" href="#categories">Shop by category</a>
            </div>
        </div>
        <div class="hero-art" aria-hidden="true">
            <div class="hero-orbit"></div><div class="hero-plinth"></div>
            <img class="hero-laptop" src="{{ asset('images/demo/laptop.webp') }}" alt="" width="640" height="480" fetchpriority="high">
            <img class="hero-phone" src="{{ asset('images/demo/phone.webp') }}" alt="" width="640" height="480">
            <img class="hero-headphones" src="{{ asset('images/demo/headphones.webp') }}" alt="" width="640" height="480">
            <img class="hero-watch" src="{{ asset('images/demo/watch.webp') }}" alt="" width="640" height="480">
            <span class="hero-art-label">01 / TECH COLLECTION</span>
        </div>
    </section>
@endif

<section class="shop-section" id="categories" aria-labelledby="category-heading">
    <div class="section-heading">
        <div><p class="eyebrow">BROWSE THE CATALOGUE</p><h2 id="category-heading">{{ $home ? 'Shop by category' : 'Categories' }}</h2></div>
        <a href="{{ route('shop.index') }}" class="text-link">All products <x-icon name="arrow-right" :size="17" /></a>
    </div>
    @if($categories->isEmpty())
        <p class="muted">Categories will appear when the catalogue is ready.</p>
    @else
        <div class="category-grid">
            @foreach($categories as $category)
                @php($icon = match ($category->slug) { 'audio' => 'headphones', 'computers' => 'laptop', 'mobiles' => 'phone', 'wearables' => 'watch', default => 'layers' })
                <a class="category-card {{ $filters->category === $category->id ? 'is-selected' : '' }}" href="{{ route('shop.index', ['category' => $category->id]) }}" @if($filters->category === $category->id) aria-current="true" @endif>
                    <span class="category-icon"><x-icon :name="$icon" :size="22" /></span>
                    <span><strong>{{ $category->name }}</strong><small>{{ $category->active_count }} {{ \Illuminate\Support\Str::plural('product', $category->active_count) }}</small></span>
                    <x-icon class="category-arrow" name="chevron" :size="15" />
                </a>
            @endforeach
        </div>
    @endif
</section>

@if($home)
    <section class="shop-section">
        <div class="section-heading"><div><p class="eyebrow">NEW IN THE STORE</p><h2>Featured products</h2></div><a class="text-link" href="{{ route('shop.index') }}">View catalogue <x-icon name="arrow-right" :size="17" /></a></div>
        @if($products->isEmpty())
            <div class="panel"><x-empty-state title="No products yet" description="Active products will appear here when added by an administrator." /></div>
        @else
            <div class="product-grid">@foreach($products as $product)<x-product-card :product="$product" />@endforeach</div>
        @endif
    </section>
@else
    <section class="shop-section" aria-label="Product catalogue">
        <div class="section-heading"><div><p class="eyebrow">PRODUCT CATALOGUE</p><h1>Browse products</h1><p class="muted">{{ $products->total() }} {{ \Illuminate\Support\Str::plural('result', $products->total()) }} · grouped by subcategory</p></div></div>
        <x-catalog-filters :categories="$categories" :filters="$filters" />
        @forelse($products->getCollection()->groupBy('subcategory_id') as $group)
            <section class="product-group">
                <div class="group-heading"><span class="section-icon"><x-icon name="layers" :size="20" /></span><div><p>{{ $group->first()->category->name }}</p><h2>{{ $group->first()->subcategory->name }}</h2></div></div>
                <div class="product-grid">@foreach($group as $product)<x-product-card :product="$product" />@endforeach</div>
            </section>
        @empty
            <div class="panel"><x-empty-state icon="search" title="No matching products" description="Try another keyword or clear the current filters."><a class="button button-secondary" href="{{ route('shop.index') }}">Clear filters</a></x-empty-state></div>
        @endforelse
        <x-pagination :paginator="$products" />
    </section>
@endif
@endsection
