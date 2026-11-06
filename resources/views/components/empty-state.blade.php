@props(['icon' => 'box', 'title', 'description' => ''])
<div class="empty-state">
    <span class="empty-icon"><x-icon :name="$icon" :size="25" /></span>
    <h2>{{ $title }}</h2>
    @if($description)<p>{{ $description }}</p>@endif
    {{ $slot }}
</div>
