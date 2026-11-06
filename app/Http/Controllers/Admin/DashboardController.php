<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Subcategory;
use App\Support\OrderStatus;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'counts' => [
                'categories' => Category::count(),
                'subcategories' => Subcategory::count(),
                'products' => Product::count(),
                'active' => Product::active()->count(),
            ],
            'recentProducts' => Product::with(['category', 'subcategory'])->latest('id')->limit(5)->get(),
            'pendingOrders' => Order::where('status', OrderStatus::Pending->value)->count(),
        ]);
    }
}
