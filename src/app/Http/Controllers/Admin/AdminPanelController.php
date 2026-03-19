<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\View\View;

class AdminPanelController extends Controller
{
    /**
     * Display the admin overview panel.
     */
    public function __invoke(): View
    {
        return view('admin.panel', [
            'categoryCount' => Category::query()->count(),
            'archivedCategoryCount' => Category::onlyTrashed()->count(),
            'productCount' => Product::query()->count(),
            'activeProductCount' => Product::query()->where('is_active', true)->count(),
            'archivedProductCount' => Product::onlyTrashed()->count(),
            'orderCount' => Order::query()->whereNotNull('placed_at')->count(),
            'archivedOrderCount' => Order::onlyTrashed()->whereNotNull('placed_at')->count(),
            'pendingOrderCount' => Order::query()->whereNotNull('placed_at')->where('status', 'pending')->count(),
            'userCount' => User::query()->count(),
            'archivedUserCount' => User::onlyTrashed()->count(),
        ]);
    }
}
