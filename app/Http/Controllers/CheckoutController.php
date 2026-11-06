<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutRequest;
use App\Models\Order;
use App\Services\Cart;
use App\Services\OrderService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    public function show(Request $request, Cart $cart): View|RedirectResponse
    {
        $quote = $cart->quote();

        if ($quote['missing']) {
            foreach ($quote['missing'] as $id) {
                $cart->remove($id);
            }

            return to_route('cart.index')->withErrors(['cart' => 'Unavailable products were removed from your cart.']);
        }

        if (! $quote['lines']) {
            return to_route('cart.index')->withErrors(['cart' => 'Add a product before placing an order.']);
        }

        $review = $request->session()->get('checkout', []);
        $token = isset($review['hash'], $review['token']) && hash_equals($review['hash'], $quote['hash'])
            ? $review['token']
            : (string) Str::uuid();
        $request->session()->put('checkout', ['token' => $token, 'hash' => $quote['hash']]);

        return view('storefront.checkout', compact('quote', 'token'));
    }

    public function store(CheckoutRequest $request, Cart $cart, OrderService $orders): RedirectResponse
    {
        $fields = $request->validated();
        $session = $request->session();
        $token = $fields['checkout_token'];
        $receipts = $session->get('receipts', []);

        if (isset($receipts[$token])) {
            return to_route('orders.receipt', $receipts[$token]);
        }

        $review = $session->get('checkout', []);

        if (! isset($review['token'], $review['hash']) || ! hash_equals($review['token'], $token)) {
            return to_route('checkout.show')->withErrors(['cart' => 'Review your cart before placing an order.']);
        }

        try {
            $order = $orders->place($cart, $fields, $review['hash']);
        } catch (UniqueConstraintViolationException $exception) {
            // If a commit succeeded but the session write did not, this token still
            // belongs to the current session. Recover the original receipt.
            $order = Order::where('checkout_token', $token)->first();
            if (! $order) {
                throw $exception;
            }
        }

        $cart->clear();
        $receipts[$token] = $order->reference;
        $session->put('receipts', array_slice($receipts, -8, null, true));

        return to_route('orders.receipt', $order->reference);
    }

    public function receipt(Request $request, string $reference): View
    {
        abort_unless(in_array($reference, $request->session()->get('receipts', []), true), 404);

        return view('storefront.receipt', [
            'order' => Order::where('reference', $reference)->with('items')->firstOrFail(),
        ]);
    }
}
