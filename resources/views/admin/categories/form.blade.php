@extends('layouts.admin')
@section('title', $category->exists ? 'Edit category' : 'New category')
@section('content')
<x-page-heading :title="$category->exists ? 'Edit category' : 'New category'" description="Set a clear name for this product group.">
    <a class="button button-secondary" href="{{ route('admin.categories.index') }}"><x-icon name="arrow-left" :size="17" /> Categories</a>
</x-page-heading>
<section class="panel form-panel narrow">
    <header class="panel-heading"><div><h2>Category details</h2><p>Required fields are marked with an asterisk.</p></div><span class="section-icon"><x-icon name="folder" :size="22" /></span></header>
    <form class="form-stack panel-body" method="post" action="{{ $category->exists ? route('admin.categories.update', $category) : route('admin.categories.store') }}" data-busy>
        @csrf
        @if($category->exists)@method('PUT')@endif
        <x-field name="name" label="Category name" :value="$category->name" maxlength="120" placeholder="e.g. Electronics" :required="true" />
        <x-field name="description" label="Description" :value="$category->description" :multiline="true" rows="4" maxlength="1000" hint="Optional. Describe what belongs in this category." />
        <div class="form-note"><x-icon name="link" :size="19" /><span>{{ $category->exists ? 'The URL identifier remains ' . $category->slug . '.' : 'A unique URL identifier is generated from the name.' }}</span></div>
        <div class="form-actions">
            <a class="button button-secondary" href="{{ route('admin.categories.index') }}">Cancel</a>
            <button type="submit" class="button button-primary" data-busy-label="Saving…">{{ $category->exists ? 'Save category' : 'Create category' }}</button>
        </div>
    </form>
</section>
@endsection
