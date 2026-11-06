@php
    $links = [
        ['admin.dashboard', 'admin.dashboard', 'home', 'Dashboard'],
        ['admin.categories.index', 'admin.categories.*', 'folder', 'Categories'],
        ['admin.subcategories.index', 'admin.subcategories.*', 'layers', 'Subcategories'],
        ['admin.products.index', 'admin.products.*', 'box', 'Products'],
        ['admin.orders.index', 'admin.orders.*', 'orders', 'Orders'],
    ];
@endphp
<nav class="admin-nav" aria-label="Administration">
    <p class="sidebar-label">WORKSPACE</p>
    @foreach($links as [$route, $pattern, $icon, $label])
        <a href="{{ route($route) }}" @class(['sidebar-link', 'is-active' => request()->routeIs($pattern)]) @if(request()->routeIs($pattern)) aria-current="page" @endif>
            <x-icon :name="$icon" :size="19" /><span>{{ $label }}</span>
            @if(request()->routeIs($pattern))<x-icon class="nav-chevron" name="chevron" :size="14" />@endif
        </a>
    @endforeach
    <span class="sidebar-rule"></span>
    <a href="{{ route('shop.home') }}" class="sidebar-link"><x-icon name="external" :size="19" /> View storefront</a>
    <form method="post" action="{{ route('logout') }}">
        @csrf
        <button class="sidebar-link" type="submit"><x-icon name="logout" :size="19" /> Log out</button>
    </form>
</nav>
