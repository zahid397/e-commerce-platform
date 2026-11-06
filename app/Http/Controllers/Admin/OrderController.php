<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use App\Support\CatalogFilter;
use App\Support\OrderStatus;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $q = CatalogFilter::text($request->query('q'));
        $filter = $request->query('status');
        $status = is_string($filter) ? OrderStatus::tryFrom($filter) : null;
        $orders = Order::withCount('items')
            ->when($status, fn ($query) => $query->where('status', $status->value))
            ->when($q !== '', fn ($query) => $query->where(function ($query) use ($q) {
                $query->whereRaw("reference LIKE ? ESCAPE '!'", [CatalogFilter::pattern($q)])
                    ->orWhereRaw("name LIKE ? ESCAPE '!'", [CatalogFilter::pattern($q)]);
            }))
            ->latest('id')->paginate(12)->withQueryString();

        return view('admin.orders.index', compact('orders', 'q', 'status'));
    }

    public function show(Order $order): View
    {
        return view('admin.orders.show', ['order' => $order->load('items')]);
    }

    public function update(Request $request, Order $order, OrderService $orders): RedirectResponse
    {
        $data = $request->validate(['status' => ['required', Rule::enum(OrderStatus::class)]]);
        $orders->transition($order, OrderStatus::from($data['status']));

        return to_route('admin.orders.show', $order)->with('success', 'Order status updated.');
    }
}
