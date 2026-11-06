@extends('layouts.admin')
@section('title', 'Subcategories')
@section('content')
<x-page-heading title="Subcategories" description="Organise related products within each category.">
    <a class="button button-primary" href="{{ route('admin.subcategories.create') }}"><x-icon name="plus" :size="18" /> New subcategory</a>
</x-page-heading>
<form method="get" action="{{ route('admin.subcategories.index') }}" class="panel filter-bar">
    <div class="filter-search"><label class="field-label" for="subcategory-search">Search subcategories</label><input class="input" name="q" id="subcategory-search" value="{{ $filters->q }}" maxlength="100" placeholder="Subcategory name"></div>
    <div><label class="field-label" for="parent-filter">Parent category</label><select class="input select" id="parent-filter" name="category"><option value="">All categories</option>
        @foreach($categories as $category)<option value="{{ $category->id }}" @selected($filters->category === $category->id)>{{ $category->name }}</option>@endforeach
    </select></div>
    <div class="filter-actions"><button class="button button-primary" type="submit">Apply</button><a class="button button-secondary" href="{{ route('admin.subcategories.index') }}">Clear</a></div>
</form>
<section class="panel">
    <header class="panel-heading"><div><h2>Subcategory list</h2><p>{{ $subcategories->total() }} {{ \Illuminate\Support\Str::plural('subcategory', $subcategories->total()) }}</p></div></header>
    @if($subcategories->isEmpty())
        <x-empty-state icon="layers" title="No subcategories found" description="Add a subcategory or choose a different filter.">
            <a class="button button-secondary" href="{{ route('admin.subcategories.create') }}">Add a subcategory</a>
        </x-empty-state>
    @else
        <div class="table-wrap">
            <table class="data-table" role="table">
                <caption class="sr-only">Subcategories</caption>
                <thead role="rowgroup"><tr role="row"><th scope="col">Name</th><th scope="col">Parent category</th><th scope="col">Products</th><th scope="col">Actions</th></tr></thead>
                <tbody role="rowgroup">
                @foreach($subcategories as $subcategory)
                    <tr role="row">
                        <td class="table-main" role="cell" data-label="Name"><div class="table-label"><strong>{{ $subcategory->name }}</strong><small>{{ $subcategory->slug }}</small></div></td>
                        <td role="cell" data-label="Parent category"><span class="mini-tag">{{ $subcategory->category->name }}</span></td>
                        <td role="cell" data-label="Products"><a class="mini-tag" href="{{ route('admin.products.index', ['category' => $subcategory->category_id, 'subcategory' => $subcategory->id]) }}">{{ $subcategory->products_count }}<span class="sr-only"> products in {{ $subcategory->name }}</span></a></td>
                        <td role="cell" data-label="Actions"><div class="row-actions"><a class="icon-button" href="{{ route('admin.subcategories.edit', $subcategory) }}" aria-label="Edit {{ $subcategory->name }}" title="Edit subcategory"><x-icon name="edit" :size="17" /></a><x-delete-form :action="route('admin.subcategories.destroy', $subcategory)" :name="$subcategory->name" /></div></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <x-pagination :paginator="$subcategories" />
    @endif
</section>
@endsection
