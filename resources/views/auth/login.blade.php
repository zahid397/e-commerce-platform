@extends('layouts.storefront')
@section('title', 'Admin sign in')
@section('content')
<div class="auth-wrap">
    <section class="auth-card panel">
        <span class="section-icon"><x-icon name="shield" :size="26" /></span>
        <p class="eyebrow">ADMIN WORKSPACE</p>
        <h1>Sign in</h1>
        <p class="page-description">Manage the catalogue and review orders.</p>
        <form method="post" action="{{ route('login.store') }}" class="form-stack" data-busy>
            @csrf
            <x-field name="email" label="Email address" type="email" autocomplete="username" maxlength="254" :required="true" />
            <x-field name="password" label="Password" type="password" autocomplete="current-password" maxlength="128" :required="true" />
            <button type="submit" class="button button-primary button-wide" data-busy-label="Signing in…">Sign in <x-icon name="arrow-right" :size="17" /></button>
        </form>
        <a class="text-link auth-back" href="{{ route('shop.home') }}"><x-icon name="arrow-left" :size="15" /> Back to storefront</a>
    </section>
</div>
@endsection
