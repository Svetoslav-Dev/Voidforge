<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LegalContactRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminLegalContactRequestController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));

        return view('admin.legal-requests.listing', [
            'requests' => LegalContactRequest::query()
                ->when($search !== '', function ($query) use ($search) {
                    $query->where(function ($builder) use ($search) {
                        $builder
                            ->where('name', 'like', '%'.$search.'%')
                            ->orWhere('email', 'like', '%'.$search.'%')
                            ->orWhere('topic', 'like', '%'.$search.'%')
                            ->orWhere('order_reference', 'like', '%'.$search.'%')
                            ->orWhere('message', 'like', '%'.$search.'%');
                    });
                })
                ->latest()
                ->paginate(15)
                ->withQueryString(),
            'search' => $search,
        ]);
    }

    public function resolve(LegalContactRequest $legalContactRequest): RedirectResponse
    {
        $legalContactRequest->forceFill([
            'resolved_at' => now(),
        ])->save();

        return redirect()
            ->route('admin.legal-requests.index')
            ->with('status', 'Request marked as resolved.');
    }

    public function reopen(LegalContactRequest $legalContactRequest): RedirectResponse
    {
        $legalContactRequest->forceFill([
            'resolved_at' => null,
        ])->save();

        return redirect()
            ->route('admin.legal-requests.index')
            ->with('status', 'Request reopened.');
    }
}
