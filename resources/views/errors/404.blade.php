@extends('layouts.storefront')
@section('title', 'Page not found')
@section('content')
<div class="panel">
    <x-empty-state icon="search" title="Page not found" description="This item may have moved or is no longer available.">
        <a href="{{ route('shop.index') }}" class="button button-primary">Browse products <x-icon name="arrow-right" :size="17" /></a>
    </x-empty-state>
</div>
@endsection
