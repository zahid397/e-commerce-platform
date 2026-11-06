<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Services\ProductService;
use App\Support\CatalogFilter;
use App\Support\DatabaseConflict;
use Illuminate\Contracts\View\View;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $filters = CatalogFilter::from($request);
        $products = $filters->products(Product::with(['category', 'subcategory']))->paginate(12)->withQueryString();
        $categories = Category::with(['subcategories' => fn ($q) => $q->orderBy('name')])->orderBy('name')->get();

        return view('admin.products.index', compact('products', 'categories', 'filters'));
    }

    public function create(): View
    {
        return $this->form(new Product(['is_active' => true]));
    }

    public function store(ProductRequest $request, ProductService $products): RedirectResponse
    {
        try {
            $products->create($request->safe()->except('image'), $request->file('image'));
        } catch (QueryException $exception) {
            $this->handleConflict($exception);
        }

        return to_route('admin.products.index')->with('success', 'Product created.');
    }

    public function show(Product $product): View
    {
        return view('admin.products.show', ['product' => $product->load(['category', 'subcategory'])]);
    }

    public function edit(Product $product): View
    {
        return $this->form($product);
    }

    public function update(ProductRequest $request, Product $product, ProductService $products): RedirectResponse
    {
        try {
            $products->update($product, $request->safe()->except('image'), $request->file('image'));
        } catch (QueryException $exception) {
            $this->handleConflict($exception);
        }

        return to_route('admin.products.index')->with('success', 'Product updated.');
    }

    public function destroy(Product $product, ProductService $products): RedirectResponse
    {
        $products->delete($product);

        return to_route('admin.products.index')->with('success', 'Product deleted.');
    }

    private function form(Product $product): View
    {
        return view('admin.products.form', [
            'product' => $product,
            'categories' => Category::with(['subcategories' => fn ($q) => $q->orderBy('name')])->orderBy('name')->get(),
        ]);
    }

    private function handleConflict(QueryException $exception): never
    {
        if (! DatabaseConflict::foreignKey($exception)) {
            throw $exception;
        }

        throw ValidationException::withMessages(['subcategory_id' => 'That category or subcategory changed. Select it again.']);
    }
}
