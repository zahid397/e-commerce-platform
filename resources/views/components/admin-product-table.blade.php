@props(['products', 'actions' => true])
<div class="table-wrap">
    <table class="data-table" role="table">
        <caption class="sr-only">Product list</caption>
        <thead role="rowgroup"><tr role="row"><th scope="col">Product</th><th scope="col">Category</th><th scope="col">Price</th><th scope="col">Status</th>@if($actions)<th scope="col">Actions</th>@endif</tr></thead>
        <tbody role="rowgroup">
        @foreach($products as $product)
            <tr role="row">
                <td class="table-main" data-label="Product" role="cell">
                    <a class="table-product" href="{{ route('admin.products.show', $product) }}">
                        <img class="table-thumb" src="{{ $product->imageUrl() }}" alt="" width="48" height="48" loading="lazy">
                        <span><strong>{{ $product->name }}</strong><small>{{ $product->subcategory->name }}</small></span>
                    </a>
                </td>
                <td data-label="Category" role="cell"><span class="mini-tag">{{ $product->category->name }}</span></td>
                <td data-label="Price" role="cell" class="nowrap">{{ \App\Support\Money::display($product->new_price) }}</td>
                <td data-label="Status" role="cell"><span class="status-badge {{ $product->is_active ? 'status-active' : 'status-draft' }}"><span class="status-dot" aria-hidden="true"></span>{{ $product->is_active ? 'Active' : 'Draft' }}</span></td>
                @if($actions)
                    <td data-label="Actions" role="cell">
                        <div class="row-actions">
                            <a class="icon-button" href="{{ route('admin.products.show', $product) }}" aria-label="View {{ $product->name }}" title="View product"><x-icon name="eye" :size="17" /></a>
                            <a class="icon-button" href="{{ route('admin.products.edit', $product) }}" aria-label="Edit {{ $product->name }}" title="Edit product"><x-icon name="edit" :size="17" /></a>
                            <x-delete-form :action="route('admin.products.destroy', $product)" :name="$product->name" />
                        </div>
                    </td>
                @endif
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
