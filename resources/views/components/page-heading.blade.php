@props(['title', 'description' => '', 'eyebrow' => 'CATALOGUE WORKSPACE'])
<div class="page-heading">
    <div>
        @if($eyebrow)<p class="eyebrow">{{ $eyebrow }}</p>@endif
        <h1>{{ $title }}</h1>
        @if($description)<p class="page-description">{{ $description }}</p>@endif
    </div>
    @if($slot->isNotEmpty())<div class="heading-actions">{{ $slot }}</div>@endif
</div>
