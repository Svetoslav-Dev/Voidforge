<?php

namespace App\Http\Controllers;

use App\Http\Requests\Legal\StoreLegalContactRequest;
use App\Mail\LegalContactRequestAcknowledgementMail;
use App\Mail\LegalContactRequestMail;
use App\Models\LegalContactRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;

class LegalContactRequestController extends Controller
{
    public function __invoke(StoreLegalContactRequest $request): RedirectResponse
    {
        $payload = $request->validated();
        LegalContactRequest::query()->create($payload);

        Mail::to((string) config('legal.support_email'))
            ->queue(new LegalContactRequestMail($payload));

        Mail::to($payload['email'])
            ->queue(new LegalContactRequestAcknowledgementMail($payload));

        return redirect()
            ->route('legal.contact')
            ->with('status', 'Your request has been sent. VoidForgeStore will review it as soon as possible.');
    }
}
