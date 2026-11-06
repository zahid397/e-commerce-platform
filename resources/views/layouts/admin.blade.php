<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'Dashboard') | E-commerce Platform</title>
    <link rel="icon" href="{{ asset('images/mark.svg') }}" type="image/svg+xml">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="admin-body">
    <a class="skip-link" href="#main">Skip to content</a>
    <div class="admin-shell">
        <aside class="sidebar">
            <x-brand :admin="true" />
            <x-admin-nav />
            <div class="sidebar-foot"><span class="online-dot"></span> Catalogue management</div>
        </aside>
        <div class="admin-main">
            <header class="admin-topbar">
                <details class="mobile-navigation" data-menu>
                    <summary class="icon-button" aria-label="Admin menu"><x-icon name="menu" /></summary>
                    <div class="mobile-navigation-panel"><x-admin-nav /></div>
                </details>
                <p class="topbar-caption"><span>Workspace</span><x-icon name="chevron" :size="13" /><strong>@yield('title', 'Dashboard')</strong></p>
                <form method="get" action="{{ route('admin.products.index') }}" class="topbar-search">
                    <input class="input" name="q" maxlength="100" placeholder="Search products" aria-label="Search admin products">
                    <button class="icon-button" type="submit" aria-label="Search"><x-icon name="search" :size="17" /></button>
                </form>
                <a class="icon-button topbar-orders" href="{{ route('admin.orders.index', ['status' => 'pending']) }}" aria-label="Pending orders" title="Pending orders"><x-icon name="orders" :size="19" /></a>
                <details class="account-menu" data-menu>
                    <summary aria-label="Account menu">
                        <span class="avatar"><x-icon name="user" :size="17" /></span>
                        <span class="account-name">{{ auth()->user()->name }}</span>
                        <x-icon name="down" :size="14" />
                    </summary>
                    <div class="account-panel">
                        <p>{{ auth()->user()->email }}</p>
                        <a class="button button-secondary button-sm" href="{{ route('shop.home') }}">View storefront <x-icon name="external" :size="15" /></a>
                        <form method="post" action="{{ route('logout') }}">
                            @csrf
                            <button class="button button-secondary button-sm" type="submit"><x-icon name="logout" :size="15" /> Log out</button>
                        </form>
                    </div>
                </details>
            </header>
            <main class="admin-content" id="main" tabindex="-1">
                <x-notice />
                @yield('content')
            </main>
            <footer class="admin-footer"><span>E-commerce Platform</span><span>Laravel catalogue workspace</span></footer>
        </div>
    </div>
</body>
</html>
