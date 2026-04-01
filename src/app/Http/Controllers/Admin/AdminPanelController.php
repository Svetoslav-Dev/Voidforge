<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\DiscountCode;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class AdminPanelController extends Controller
{
    /**
     * Display the admin overview panel.
     */
    public function __invoke(): View
    {
        $startOfYear = Carbon::now()->startOfYear();
        $endOfYear = Carbon::now()->endOfYear();
        $startOfLastYear = $startOfYear->copy()->subYear();
        $endOfLastYear = $endOfYear->copy()->subYear();

        $paidOrdersThisYear = Order::query()
            ->where('status', 'paid')
            ->whereBetween('placed_at', [$startOfYear, $endOfYear])
            ->get(['placed_at', 'total_cents']);

        $paidOrdersLastYear = Order::query()
            ->where('status', 'paid')
            ->whereBetween('placed_at', [$startOfLastYear, $endOfLastYear])
            ->get(['placed_at', 'total_cents']);

        $monthlyRevenue = collect(range(1, 12))
            ->map(function (int $month) use ($paidOrdersThisYear) {
                $totalCents = $paidOrdersThisYear
                    ->filter(fn (Order $order) => $order->placed_at?->month === $month)
                    ->sum('total_cents');

                return [
                    'label' => Carbon::create()->month($month)->format('M'),
                    'total_cents' => $totalCents,
                ];
            });

        $monthlyRevenueLastYear = collect(range(1, 12))
            ->map(function (int $month) use ($paidOrdersLastYear) {
                $totalCents = $paidOrdersLastYear
                    ->filter(fn (Order $order) => $order->placed_at?->month === $month)
                    ->sum('total_cents');

                return [
                    'label' => Carbon::create()->month($month)->format('M'),
                    'total_cents' => $totalCents,
                ];
            });

        $topShirts = OrderItem::query()
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.status', 'paid')
            ->whereNull('orders.deleted_at')
            ->selectRaw('product_name, SUM(quantity) as total_sold')
            ->groupBy('product_name')
            ->orderByDesc('total_sold')
            ->limit(10)
            ->get();

        $topCategories = OrderItem::query()
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->where('orders.status', 'paid')
            ->whereNull('orders.deleted_at')
            ->whereNotNull('order_items.product_id')
            ->selectRaw('categories.name as category_name, SUM(order_items.quantity) as total_sold')
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('total_sold')
            ->limit(3)
            ->get();

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
            'discountCodeCount' => DiscountCode::query()->count(),
            'revenueChartYear' => $startOfYear->year,
            'revenueChartPreviousYear' => $startOfLastYear->year,
            'monthlyRevenue' => $monthlyRevenue,
            'monthlyRevenueLastYear' => $monthlyRevenueLastYear,
            'yearlyRevenueTotalCents' => $monthlyRevenue->sum('total_cents'),
            'yearlyRevenuePreviousTotalCents' => $monthlyRevenueLastYear->sum('total_cents'),
            'topShirts' => $topShirts,
            'topCategories' => $topCategories,
            'yearlyRevenueMaxCents' => max(
                1,
                (int) max($monthlyRevenue->max('total_cents'), $monthlyRevenueLastYear->max('total_cents'))
            ),
        ]);
    }
}
