@extends('layouts.storefront')
@section('title', 'Session expired')
@section('content')
<div class="panel">
    <x-empty-state icon="shield" title="Session expired" description="Go back to the storefront or sign in again, then retry your action.">
        <a href="{{ route('shop.home') }}" class="button button-primary">Back to storefront</a>
    </x-empty-state>
</div>
@endsection
