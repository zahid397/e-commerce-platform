@props(['name', 'size' => 20])
<svg width="{{ $size }}" height="{{ $size }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false" {{ $attributes }}>
@switch($name)
    @case('bag') <path d="M5 8h14l1.5 13h-17L5 8Z"/><path d="M9 9V6a3 3 0 0 1 6 0v3"/> @break
    @case('home') <path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1Z"/><path d="M9 21v-8h6v8"/> @break
    @case('grid') <rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/> @break
    @case('folder') <path d="M3 7a2 2 0 0 1 2-2h5l2 2h7a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2Z"/> @break
    @case('layers') <path d="m12 3 9 5-9 5-9-5 9-5Zm-9 9 9 5 9-5M3 16l9 5 9-5"/> @break
    @case('box') <path d="m12 3 9 5v8l-9 5-9-5V8l9-5Zm0 9v9M3 8l9 5 9-5M8 5l9 5"/> @break
    @case('tag') <path d="M4 11V4h7l9 9-7 7-9-9Z"/><circle cx="8" cy="8" r="1"/> @break
    @case('orders') <rect x="5" y="4" width="14" height="17" rx="2"/><path d="M9 4V2h6v2M9 10h6m-6 4h6m-6 4h3"/> @break
    @case('search') <circle cx="10.8" cy="10.8" r="6.8"/><path d="m16 16 4.5 4.5"/> @break
    @case('heart') <path d="M20.8 8.8c0 4.3-8.8 9.3-8.8 9.3S3.2 13.1 3.2 8.8a4.6 4.6 0 0 1 8.8-2 4.6 4.6 0 0 1 8.8 2Z"/> @break
    @case('cart') <path d="M2 3h3l2.3 12h11.6l2-8H6"/><circle cx="9" cy="20" r="1"/><circle cx="18" cy="20" r="1"/> @break
    @case('phone') <rect x="6" y="2" width="12" height="20" rx="2"/><path d="M11 18h2"/> @break
    @case('watch') <rect x="6" y="7" width="12" height="10" rx="2"/><path d="m8 7 1-5h6l1 5m-8 10 1 5h6l1-5m-4-7v2h2"/> @break
    @case('laptop') <path d="M5 17V5h14v12M3 17h18l1 3H2l1-3Z"/> @break
    @case('headphones') <path d="M3 14v-2a9 9 0 0 1 18 0v2"/><rect x="3" y="13" width="4" height="7" rx="2"/><rect x="17" y="13" width="4" height="7" rx="2"/> @break
    @case('arrow-right') <path d="M4 12h15m-6-6 6 6-6 6"/> @break
    @case('arrow-left') <path d="M20 12H5m6-6-6 6 6 6"/> @break
    @case('external') <path d="M9 6h9v9m0-9L6 18"/> @break
    @case('chevron') <path d="m9 6 6 6-6 6"/> @break
    @case('down') <path d="m6 9 6 6 6-6"/> @break
    @case('plus') <path d="M12 5v14M5 12h14"/> @break
    @case('minus') <path d="M5 12h14"/> @break
    @case('edit') <path d="m4 16-1 5 5-1L21 7l-4-4-13 13Zm10-10 4 4"/> @break
    @case('trash') <path d="M3 6h18M8 6V3h8v3M5 6l1 15h12l1-15M10 10v7m4-7v7"/> @break
    @case('eye') <path d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="2.5"/> @break
    @case('user') <circle cx="12" cy="8" r="3.3"/><path d="M4 21v-2a6 6 0 0 1 6-6h4a6 6 0 0 1 6 6v2"/> @break
    @case('logout') <path d="M10 21H4V3h6m4 5 4 4-4 4m-7-4h11"/> @break
    @case('menu') <path d="M3 6h18M3 12h18M3 18h18"/> @break
    @case('check') <path d="m5 12 4 4L19 6"/> @break
    @case('alert') <path d="M12 9v4m0 3h.01"/><path d="m12 3 10 18H2L12 3Z"/> @break
    @case('bolt') <path d="m13 2-9 12h7l-1 8 10-12h-7l0-8Z"/> @break
    @case('upload') <path d="M12 15V3m-5 5 5-5 5 5M4 15v5h16v-5"/> @break
    @case('image') <rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="m21 15-5-5L5 21"/> @break
    @case('shield') <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/><path d="m9 12 2 2 4-4"/> @break
    @case('clock') <circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/> @break
    @case('link') <path d="m10 13 4-4m-6 6-2 2a3 3 0 0 1-4-4l4-4a3 3 0 0 1 4 0m4 0 2-2a3 3 0 0 1 4 4l-4 4a3 3 0 0 1-4 0"/> @break
    @default <circle cx="12" cy="12" r="8"/>
@endswitch
</svg>
