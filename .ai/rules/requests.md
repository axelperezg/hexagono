---
paths:
  - 'app/Http/Requests/**'
---

# Requests

## Contact form spam gate: honeypot + render-timestamp, and a decrypt() trap
The public contact form (`resources/views/welcome.blade.php`) carries two anti-spam fields not covered by `StoreContactMessageRequest::rules()`: a hidden honeypot input (`website`) and an encrypted render timestamp (`rendered_at`). `StoreContactMessageRequest::looksLikeSpam()` checks both. `ContactController::store()` calls it after validation passes and, if true, skips persisting/emailing but still returns the normal success response — so a bot gets no signal telling it the message was discarded. Keep new signals here, not surfaced as validation errors.

Trap: `decrypt()` (and `Crypt::decrypt()`) rejects an `Illuminate\Support\Stringable` argument (e.g. from `$request->string(...)`) with "The payload is invalid", even though it looks like a plain string — cast to `(string)` first, or read via `$request->input(...)` directly.
