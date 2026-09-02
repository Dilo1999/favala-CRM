<?php

namespace App\Http\Controllers;

use App\Mail\QuoteRequestMail;
use App\Services\SeoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class QuoteController extends Controller
{
    public function show(SeoService $seo): View
    {
        $seo->applyForPage('quote', [
            'meta_title' => 'Get a Quote',
            'meta_description' => 'Request a quote or demo for Favala CRM. Tell us about your business and how we can help.',
        ]);

        return view('pages.quote');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'company' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'industry' => ['nullable', 'string', 'max:255'],
            'details' => ['nullable', 'string', 'max:20000'],
            'file' => ['nullable', 'file', 'max:10240'],
        ]);

        $file = $request->file('file');

        Mail::to(config('mail.contact_to', config('mail.from.address')))
            ->send(new QuoteRequestMail(
                name: $validated['name'],
                company: $validated['company'] ?? null,
                email: $validated['email'],
                phone: $validated['phone'] ?? null,
                industry: $validated['industry'] ?? null,
                details: $validated['details'] ?? null,
                file: $file,
            ));

        return redirect()->route('quote')->with('quote_success', true);
    }
}

