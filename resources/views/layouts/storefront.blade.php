<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="@yield('description', 'Browse electronics, wearables and computer accessories on E-commerce Platform.')">
    <title>@yield('title', 'Discover the catalogue') | E-commerce Platform</title>
    @hasSection('canonical')<link rel="canonical" href="@yield('canonical')">@endif
    <link rel="icon" href="{{ asset('images/mark.svg') }}" type="image/svg+xml">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="shop-body">
    <a class="skip-link" href="#main">Skip to content</a>
    <header class="shop-header">
        <div class="site-container shop-header-inner">
            <x-brand />
            <nav class="shop-nav" aria-label="Storefront">
                <a href="{{ route('shop.home') }}" @if(request()->routeIs('shop.home')) aria-current="page" @endif>Home</a>
                <a href="{{ route('shop.home') }}#categories">Categories</a>
                <a href="{{ route('shop.index') }}" @if(request()->routeIs('shop.index', 'shop.products.show')) aria-current="page" @endif>Products</a>
            </nav>
            <form method="get" action="{{ route('shop.index') }}" class="header-search" role="search">
                <input name="q" aria-label="Search the catalogue" maxlength="100" placeholder="Search products…" value="{{ \App\Support\CatalogFilter::text(request()->query('q')) }}">
                <button type="submit" class="icon-button" aria-label="Search catalogue"><x-icon name="search" :size="18" /></button>
            </form>
            <div class="shop-tools">
                <a class="icon-button" href="{{ route('favorites.index') }}" aria-label="Saved products" title="Saved products"><x-icon name="heart" :size="21" /></a>
                <a class="icon-button cart-link" href="{{ route('cart.index') }}" aria-label="Cart ({{ array_sum(session('cart', [])) }} items)" title="Cart">
                    <x-icon name="cart" :size="21" />
                    @if(session('cart'))<span class="cart-count">{{ array_sum(session('cart', [])) }}</span>@endif
                </a>
            </div>
        </div>
    </header>
    <main class="site-container shop-content" id="main" tabindex="-1">
        <x-notice />
        @yield('content')
    </main>
    <footer class="shop-footer">
        <div class="site-container footer-inner">
            <div><x-brand /><p>A simple catalogue for everyday tech.</p></div>
            <nav aria-label="Footer">
                <a href="{{ route('shop.index') }}">Browse products</a>
                <a href="{{ route('favorites.index') }}">Saved products</a>
                @can('manage-catalog')
                    <a href="{{ route('admin.dashboard') }}">Admin dashboard <x-icon name="external" :size="14" /></a>
                @else
                    <a href="{{ route('login') }}">Admin sign in <x-icon name="external" :size="14" /></a>
                @endcan
            </nav>
        </div>
        <div class="site-container footer-bottom"><span>Catalogue & ordering demo</span><span>All prices in BDT</span></div>
    </footer>
</body>
</html>
