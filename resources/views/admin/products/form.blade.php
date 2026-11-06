@extends('layouts.admin')
@section('title', $product->exists ? 'Edit product' : 'New product')
@section('content')
<x-page-heading :title="$product->exists ? 'Edit product' : 'New product'" description="Describe the product and decide how it appears in the store.">
    <a class="button button-secondary" href="{{ route('admin.products.index') }}"><x-icon name="arrow-left" :size="17" /> Products</a>
</x-page-heading>
@if($categories->sum(fn ($category) => $category->subcategories->count()) === 0)
    <section class="panel">
        <x-empty-state icon="layers" title="Create a subcategory first" description="A product needs both a category and a subcategory.">
            <a class="button button-primary" href="{{ route('admin.subcategories.create') }}">New subcategory</a>
        </x-empty-state>
    </section>
@else
    <form class="product-edit-grid" method="post" enctype="multipart/form-data" action="{{ $product->exists ? route('admin.products.update', $product) : route('admin.products.store') }}" data-category-filter data-busy>
        @csrf
        @if($product->exists)@method('PUT')@endif
        <section class="panel">
            <header class="panel-heading"><div><h2>Product information</h2><p>Required fields are marked with an asterisk.</p></div></header>
            <div class="panel-body form-stack">
                <x-field name="name" label="Product name" :value="$product->name" maxlength="150" :required="true" placeholder="e.g. Wireless Studio Headphones" />
                <div class="form-pair">
                    <x-select-field name="category_id" label="Category" :required="true" data-parent-select>
                        <option value="">Choose a category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" @selected(\App\Support\OldInput::string('category_id', $product->category_id) === (string) $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </x-select-field>
                    <x-select-field name="subcategory_id" label="Subcategory" :required="true" data-child-select data-require-parent>
                        <option value="">Choose a subcategory</option>
                        @foreach($categories as $category)
                            <optgroup label="{{ $category->name }}">
                                @foreach($category->subcategories as $subcategory)
                                    <option data-category="{{ $category->id }}" value="{{ $subcategory->id }}" @selected(\App\Support\OldInput::string('subcategory_id', $product->subcategory_id) === (string) $subcategory->id)>{{ $subcategory->name }}</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </x-select-field>
                </div>
                <x-field name="description" label="Description" :value="$product->description" :multiline="true" rows="6" maxlength="6000" :required="true" placeholder="Explain what the product is and how it is used." />
                <div class="form-pair">
                    <x-field name="new_price" label="New price (BDT)" :value="$product->new_price" inputmode="decimal" maxlength="11" placeholder="0.00" :required="true" />
                    <x-field name="old_price" label="Old price (BDT)" :value="$product->old_price" inputmode="decimal" maxlength="11" placeholder="0.00" hint="Optional. At least the new price." />
                </div>
                <x-select-field name="is_active" label="Storefront status" :required="true">
                    <option value="1" @selected(\App\Support\OldInput::string('is_active', (int) $product->is_active) === '1')>Active — visible in the store</option>
                    <option value="0" @selected(\App\Support\OldInput::string('is_active', (int) $product->is_active) === '0')>Draft — hidden from the store</option>
                </x-select-field>
            </div>
        </section>
        <aside class="form-aside">
            <section class="panel">
                <header class="panel-heading"><div><h2>Product image</h2><p>JPEG, PNG or WebP</p></div><x-icon name="image" :size="21" /></header>
                <div class="panel-body form-stack">
                    <div class="image-preview"><img src="{{ $product->imageUrl() }}" alt="Product image preview" width="640" height="480" data-image-preview></div>
                    <x-field name="image" label="Choose image" type="file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" :required="! $product->exists" hint="Up to 3 MB and 3,200 × 3,200 pixels." data-image-input />
                    @if($product->exists)<p class="field-hint">Leave this blank to keep the current image.</p>@endif
                </div>
            </section>
            <section class="panel url-panel panel-body"><span class="section-icon"><x-icon name="link" :size="22" /></span><div><h2>Product URL</h2><p>{{ $product->exists ? '/products/' . $product->slug : 'A unique slug is generated on save.' }}</p></div></section>
        </aside>
        <div class="form-actions product-form-actions">
            <a class="button button-secondary" href="{{ route('admin.products.index') }}">Cancel</a>
            <button class="button button-primary" type="submit" data-busy-label="Saving…"><x-icon name="check" :size="17" /> {{ $product->exists ? 'Save product' : 'Create product' }}</button>
        </div>
    </form>
@endif
@endsection
