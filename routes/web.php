<?php

use App\Http\Controllers\ContactController;
use App\Livewire\ContactMessages\Index as ContactMessagesIndex;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

// Public contact form on the landing page (resources/views/welcome.blade.php).
Route::post('/contacto', [ContactController::class, 'store'])->name('contact.store');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    // Internal inbox for messages submitted through the public contact form.
    Route::livewire('mensajes-contacto', ContactMessagesIndex::class)->name('contact-messages.index');
});

require __DIR__.'/settings.php';
