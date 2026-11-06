@props(['categories', 'filters', 'admin' => false])
<form class="filter-bar {{ $admin ? 'panel' : 'store-filters' }}" method="get" action="{{ route($admin ? 'admin.products.index' : 'shop.index') }}" data-category-filter>
    <div class="filter-search">
        <label class="field-label" for="filter-q">Search</label>
        <input class="input" id="filter-q" name="q" value="{{ $filters->q }}" placeholder="Product name" maxlength="100">
    </div>
    <div>
        <label class="field-label" for="filter-category">Category</label>
        <select class="input select" id="filter-category" name="category" data-parent-select>
            <option value="">All categories</option>
            @foreach($categories as $category)
                <option value="{{ $category->id }}" @selected($filters->category === $category->id)>{{ $category->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="field-label" for="filter-subcategory">Subcategory</label>
        <select class="input select" id="filter-subcategory" name="subcategory" data-child-select>
            <option value="">All subcategories</option>
            @foreach($categories as $category)
                <optgroup label="{{ $category->name }}">
                    @foreach($category->subcategories as $subcategory)
                        <option data-category="{{ $category->id }}" value="{{ $subcategory->id }}" @selected($filters->subcategory === $subcategory->id)>{{ $subcategory->name }}</option>
                    @endforeach
                </optgroup>
            @endforeach
        </select>
    </div>
    <div>
        <label class="field-label" for="filter-sort">Sort</label>
        <select class="input select" id="filter-sort" name="sort">
            @foreach(\App\Support\CatalogFilter::SORTS as $value => $label)
                <option value="{{ $value }}" @selected($filters->sort === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    @if($admin)
        <div>
            <label class="field-label" for="filter-state">Status</label>
            <select class="input select" id="filter-state" name="state">
                <option value="">All statuses</option>
                <option value="active" @selected($filters->state === 'active')>Active</option>
                <option value="draft" @selected($filters->state === 'draft')>Draft</option>
            </select>
        </div>
    @endif
    <div class="filter-actions">
        <button class="button button-primary" type="submit"><x-icon name="search" :size="16" /> Apply</button>
        <a href="{{ route($admin ? 'admin.products.index' : 'shop.index') }}" class="button button-secondary">Clear</a>
    </div>
</form>
