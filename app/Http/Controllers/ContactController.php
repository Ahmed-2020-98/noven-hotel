<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactRequest;
use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function show(): View
    {
        return view('pages.contact');
    }

    public function store(StoreContactRequest $request): RedirectResponse
    {
        ContactMessage::create([...$request->safe()->except('website'), 'ip_address' => $request->ip()]);

        return back()->with('status', __('site.contact.sent'));
    }
}
