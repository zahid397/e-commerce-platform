@props(['admin' => false])
<a class="brand" href="{{ route($admin ? 'admin.dashboard' : 'shop.home') }}">
    <span class="brand-mark"><x-icon name="bag" :size="22" /></span>
    <span class="brand-type">E-commerce <strong>Platform</strong>@if($admin)<small>ADMIN WORKSPACE</small>@endif</span>
</a>
