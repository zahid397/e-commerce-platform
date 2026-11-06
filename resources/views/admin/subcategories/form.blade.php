@extends('layouts.admin')
@section('title', $subcategory->exists ? 'Edit subcategory' : 'New subcategory')
@section('content')
<x-page-heading :title="$subcategory->exists ? 'Edit subcategory' : 'New subcategory'" description="Every subcategory belongs to one category.">
    <a class="button button-secondary" href="{{ route('admin.subcategories.index') }}"><x-icon name="arrow-left" :size="17" /> Subcategories</a>
</x-page-heading>
<section class="panel form-panel narrow">
    @if($categories->isEmpty())
        <x-empty-state icon="folder" title="Create a category first" description="Choose a parent category before adding a subcategory.">
            <a class="button button-primary" href="{{ route('admin.categories.create') }}">New category</a>
        </x-empty-state>
    @else
        <header class="panel-heading"><div><h2>Subcategory details</h2><p>Required fields are marked with an asterisk.</p></div><span class="section-icon"><x-icon name="layers" :size="22" /></span></header>
        <form class="form-stack panel-body" method="post" action="{{ $subcategory->exists ? route('admin.subcategories.update', $subcategory) : route('admin.subcategories.store') }}" data-busy>
            @csrf
            @if($subcategory->exists)@method('PUT')@endif
            <x-field name="name" label="Subcategory name" :value="$subcategory->name" maxlength="120" placeholder="e.g. Headphones" :required="true" />
            <x-select-field name="category_id" label="Parent category" :required="true">
                <option value="">Choose a category</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" @selected(\App\Support\OldInput::string('category_id', $subcategory->category_id) === (string) $category->id)>{{ $category->name }}</option>
                @endforeach
            </x-select-field>
            @if($subcategory->exists)<p class="form-note"><x-icon name="link" :size="19" /> URL identifier: {{ $subcategory->slug }}</p>@endif
            <div class="form-actions">
                <a class="button button-secondary" href="{{ route('admin.subcategories.index') }}">Cancel</a>
                <button class="button button-primary" type="submit" data-busy-label="Saving…">{{ $subcategory->exists ? 'Save subcategory' : 'Create subcategory' }}</button>
            </div>
        </form>
    @endif
</section>
@endsection
