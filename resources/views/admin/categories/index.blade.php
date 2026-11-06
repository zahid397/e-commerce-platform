@extends('layouts.admin')
@section('title', 'Categories')
@section('content')
<x-page-heading title="Categories" description="Top-level groups for your products.">
    <a class="button button-primary" href="{{ route('admin.categories.create') }}"><x-icon name="plus" :size="18" /> New category</a>
</x-page-heading>
<form method="get" action="{{ route('admin.categories.index') }}" class="panel filter-bar">
    <div class="filter-search"><label class="field-label" for="category-search">Search categories</label><input class="input" name="q" id="category-search" value="{{ $q }}" maxlength="100" placeholder="Category name"></div>
    <div class="filter-actions"><button class="button button-primary" type="submit">Search</button><a class="button button-secondary" href="{{ route('admin.categories.index') }}">Clear</a></div>
</form>
<section class="panel">
    <header class="panel-heading"><div><h2>Category list</h2><p>{{ $categories->total() }} {{ \Illuminate\Support\Str::plural('category', $categories->total()) }}</p></div></header>
    @if($categories->isEmpty())
        <x-empty-state icon="folder" title="No categories found" description="Create a category or clear your search.">
            <a class="button button-secondary" href="{{ route('admin.categories.create') }}">Add a category</a>
        </x-empty-state>
    @else
        <div class="table-wrap">
            <table class="data-table" role="table">
                <caption class="sr-only">Categories</caption>
                <thead role="rowgroup"><tr role="row"><th scope="col">Name</th><th scope="col">Subcategories</th><th scope="col">Products</th><th scope="col">Actions</th></tr></thead>
                <tbody role="rowgroup">
                @foreach($categories as $category)
                    <tr role="row">
                        <td class="table-main" role="cell" data-label="Name"><div class="table-label"><strong>{{ $category->name }}</strong><small>{{ $category->slug }}</small>@if($category->description)<small>{{ \Illuminate\Support\Str::limit($category->description, 90) }}</small>@endif</div></td>
                        <td role="cell" data-label="Subcategories"><a class="mini-tag" href="{{ route('admin.subcategories.index', ['category' => $category->id]) }}">{{ $category->subcategories_count }}<span class="sr-only"> subcategories in {{ $category->name }}</span></a></td>
                        <td role="cell" data-label="Products"><a class="mini-tag" href="{{ route('admin.products.index', ['category' => $category->id]) }}">{{ $category->products_count }}<span class="sr-only"> products in {{ $category->name }}</span></a></td>
                        <td role="cell" data-label="Actions"><div class="row-actions"><a class="icon-button" href="{{ route('admin.categories.edit', $category) }}" aria-label="Edit {{ $category->name }}" title="Edit category"><x-icon name="edit" :size="17" /></a><x-delete-form :action="route('admin.categories.destroy', $category)" :name="$category->name" /></div></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <x-pagination :paginator="$categories" />
    @endif
</section>
@endsection
