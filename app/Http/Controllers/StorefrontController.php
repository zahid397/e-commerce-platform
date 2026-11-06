<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Support\CatalogFilter;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class StorefrontController extends Controller
{
    public function index(Request $request): View
    {
        $filters = CatalogFilter::from($request);
        $home = $request->routeIs('shop.home') && $filters->q === '' && $filters->category === null && $filters->subcategory === null;
        $categories = Category::orderBy('name')
            ->withCount(['products as active_count' => fn ($q) => $q->active()])
            ->with(['subcategories' => fn ($q) => $q->orderBy('name')])
            ->get();
        $query = Product::with(['category', 'subcategory']);
        $products = $home
            ? $query->active()->latest('id')->limit(8)->get()
            : $filters->products($query, public: true)->paginate(12)->withQueryString();

        return view('storefront.index', compact('home', 'categories', 'products', 'filters'));
    }

    public function show(Product $product): View
    {
        abort_unless($product->is_active, 404);
        $product->load(['category', 'subcategory']);
        $related = Product::active()->with(['category', 'subcategory'])
            ->where('subcategory_id', $product->subcategory_id)
            ->where('id', '<>', $product->id)
            ->latest('id')->limit(4)->get();

        return view('storefront.show', compact('product', 'related'));
    }
}
