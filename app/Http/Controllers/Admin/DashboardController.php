<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
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
                'revenue' => Order::whereIn('status', ['delivered', 'completed'])->sum('total'),
                'customers' => User::where('role', 'user')->count(),
            ],
            'recentOrders' => Order::with('user')->latest()->limit(6)->get(),
            'ordersByDay' => Order::where('created_at', '>=', now()->subDays(13))->selectRaw('DATE(created_at) as day, COUNT(*) as orders, SUM(total) as revenue')->groupBy('day')->orderBy('day')->get(),
            'topProducts' => OrderItem::selectRaw('product_name, SUM(quantity) as units, SUM(subtotal) as revenue')->groupBy('product_name')->orderByDesc('units')->limit(5)->get(),
            'lowStockProducts' => Product::where('stock', '<=', 5)->orderBy('stock')->limit(8)->get(),
        ]);
    }
}
