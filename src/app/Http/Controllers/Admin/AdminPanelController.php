<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\DiscountCode;
use App\Models\Order;
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
            'yearlyRevenueMaxCents' => max(
                1,
                (int) max($monthlyRevenue->max('total_cents'), $monthlyRevenueLastYear->max('total_cents'))
            ),
        ]);
    }
}
