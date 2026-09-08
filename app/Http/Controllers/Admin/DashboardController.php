<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('admin.dashboard', [
            'stats' => [
                'products' => Product::count(),
                'categories' => Category::count(),
                'orders' => Order::count(),
                'pending' => Order::where('status', 'pending')->count(),
                'lowStock' => Product::where('stock', '<=', 5)->count(),
                'revenue' => Order::where('status', 'completed')->sum('total'),
            ],
            'recentOrders' => Order::with('user')->latest()->limit(6)->get(),
        ]);
    }
}
