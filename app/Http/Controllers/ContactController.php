<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactMessageRequest;
use App\Models\ContactMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class ContactController extends Controller
{
    /**
     * Store a message submitted through the public contact form.
     *
     * Supports two flows from the same endpoint:
     *  - A normal (non-AJAX) form submission: redirects back with a
     *    flashed "success" message, rendered by the Blade view.
     *  - A fetch()-based submission (see resources/js/landing.js), which
     *    sends an "Accept: application/json" header: returns JSON so the
     *    page can show the result without a full reload.
     */
    public function store(StoreContactMessageRequest $request): RedirectResponse|JsonResponse
    {
        $contactMessage = ContactMessage::create($request->validated());

        // Notify the internal team by email. Uncomment once outbound mail
        // is configured (see .env: MAIL_MAILER, MAIL_HOST, MAIL_TO_ADDRESS).
        // This project ships with MAIL_MAILER=smtp pointed at a local
        // Mailpit-style catcher, so it is safe to enable in development.
        //
        // Mail::to(config('mail.to_address'))->send(
        //     new ContactMessageReceived($contactMessage)
        // );

        $message = 'Gracias por contactarnos. Nuestro equipo revisará tu solicitud y te responderá a la brevedad.';

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
            ]);
        }

        return back()->with('success', $message);
    }
}
