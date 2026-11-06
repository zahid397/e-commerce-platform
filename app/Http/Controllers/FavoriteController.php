<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class FavoriteController extends Controller
{
    public function index(Request $request): View
    {
        $products = Product::active()->with(['category', 'subcategory'])
            ->whereKey($request->session()->get('favorites', []))
            ->latest('id')->paginate(12);

        return view('storefront.favorites', compact('products'));
    }

    public function toggle(Request $request, Product $product): RedirectResponse
    {
        $ids = $request->session()->get('favorites', []);

        if (in_array($product->id, $ids, true)) {
            $request->session()->put('favorites', array_values(array_diff($ids, [$product->id])));

            return back()->with('success', 'Removed from saved products.');
        }

        abort_unless($product->is_active, 404);

        if (count($ids) >= 100) {
            throw ValidationException::withMessages(['favorites' => 'Save up to 100 products. Remove one before adding another.']);
        }

        $ids[] = $product->id;
        $request->session()->put('favorites', $ids);

        return back()->with('success', 'Product saved.');
    }
}
