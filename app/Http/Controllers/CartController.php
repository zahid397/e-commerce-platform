<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\Cart;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index(Cart $cart): View
    {
        return view('storefront.cart', ['quote' => $cart->quote()]);
    }

    public function store(Request $request, Product $product, Cart $cart): RedirectResponse
    {
        abort_unless($product->is_active, 404);
        $quantity = $this->quantity($request);
        $cart->set($product->id, $quantity, add: true);

        return to_route('cart.index')->with('success', 'Added to your cart.');
    }

    public function update(Request $request, Product $product, Cart $cart): RedirectResponse
    {
        abort_unless($product->is_active && isset($cart->quantities()[$product->id]), 404);
        $cart->set($product->id, $this->quantity($request));

        return to_route('cart.index')->with('success', 'Quantity updated.');
    }

    public function destroy(string $productId, Cart $cart): RedirectResponse
    {
        $cart->remove((int) $productId);

        return to_route('cart.index')->with('success', 'Item removed.');
    }

    private function quantity(Request $request): int
    {
        return (int) $request->validate(['quantity' => ['required', 'integer', 'min:1', 'max:'.Cart::MAX_QUANTITY]])['quantity'];
    }
}
