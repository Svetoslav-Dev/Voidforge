<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreDiscountCodeRequest;
use App\Http\Requests\Admin\UpdateDiscountCodeRequest;
use App\Models\DiscountCode;
use App\Services\AdminDiscountCodeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminDiscountCodeController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));

        return view('admin.discount-codes.listing', [
            'discountCodes' => DiscountCode::query()
                ->when($search !== '', function ($query) use ($search) {
                    $query->where(function ($builder) use ($search) {
                        $builder
                            ->where('code', 'like', '%'.$search.'%')
                            ->orWhere('description', 'like', '%'.$search.'%');
                    });
                })
                ->orderBy('code')
                ->paginate(15)
                ->withQueryString(),
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        return view('admin.discount-codes.create');
    }

    public function store(StoreDiscountCodeRequest $request, AdminDiscountCodeService $discountCodes): RedirectResponse
    {
        $discountCodes->create($request->validated());

        return redirect()
            ->route('admin.discount-codes.index')
            ->with('status', 'Discount code created.');
    }

    public function edit(DiscountCode $discountCode): View
    {
        return view('admin.discount-codes.edit', [
            'discountCode' => $discountCode,
        ]);
    }

    public function update(UpdateDiscountCodeRequest $request, DiscountCode $discountCode, AdminDiscountCodeService $discountCodes): RedirectResponse
    {
        $discountCodes->update($discountCode, $request->validated());

        return redirect()
            ->route('admin.discount-codes.index')
            ->with('status', 'Discount code updated.');
    }

    public function archive(DiscountCode $discountCode, AdminDiscountCodeService $discountCodes): RedirectResponse
    {
        $discountCodes->archive($discountCode);

        return redirect()
            ->route('admin.discount-codes.index')
            ->with('status', 'Discount code archived.');
    }

    public function restore(DiscountCode $discountCode, AdminDiscountCodeService $discountCodes): RedirectResponse
    {
        $discountCodes->restore($discountCode);

        return redirect()
            ->route('admin.discount-codes.index')
            ->with('status', 'Discount code restored.');
    }
}
