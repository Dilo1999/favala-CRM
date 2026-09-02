<?php

namespace App\Http\Controllers;

use App\Mail\ContactFormMail;
use App\Services\SeoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function show(SeoService $seo): View
    {
        $seo->applyForPage('contact', [
            'meta_title' => 'Contact Us',
            'meta_description' => 'Contact the Favala CRM team for support, demos, and general inquiries.',
        ]);

        return view('pages.contact');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:10000'],
        ], [
            'name.required' => 'Please enter your name.',
            'email.required' => 'Please enter your email address.',
            'email.email' => 'Please enter a valid email address.',
            'subject.required' => 'Please enter a subject.',
            'message.required' => 'Please enter your message.',
        ]);

        Mail::to(config('mail.contact_to', config('mail.from.address')))
            ->send(new ContactFormMail(
                senderName: $validated['name'],
                senderEmail: $validated['email'],
                formSubject: $validated['subject'],
                messageBody: $validated['message'],
            ));

        return redirect()->route('contact')->with('contact_success', true);
    }
}
