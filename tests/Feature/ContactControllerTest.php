<?php

use App\Enums\StudyType;
use App\Models\ContactMessage;

it('renders the landing page with the contact form', function () {
    $response = $this->get(route('home'));

    $response->assertOk();
    $response->assertSee('Hexágono Research');
    $response->assertSee(route('contact.store'), escape: false);
});

it('stores a valid contact message and redirects back with a success message', function () {
    $payload = [
        'name' => 'Ana Torres',
        'institution' => 'Secretaría de Ejemplo',
        'email' => 'ana.torres@example.com',
        'phone' => '5555555555',
        'study_type' => StudyType::Pretest->value,
        'message' => 'Nos interesa un estudio pre-test para nuestra próxima campaña.',
    ];

    $response = $this->from(route('home'))->post(route('contact.store'), $payload);

    $response->assertRedirect(route('home'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('contact_messages', [
        'name' => 'Ana Torres',
        'email' => 'ana.torres@example.com',
        'study_type' => StudyType::Pretest->value,
    ]);
});

it('stores a valid contact message and returns json when the request expects json', function () {
    $payload = [
        'name' => 'Luis Pérez',
        'email' => 'luis.perez@example.com',
        'study_type' => StudyType::Opinion->value,
        'message' => 'Quisiéramos cotizar un estudio de opinión pública.',
    ];

    $response = $this->postJson(route('contact.store'), $payload);

    $response->assertOk();
    $response->assertJson(['success' => true]);

    $this->assertDatabaseHas('contact_messages', [
        'email' => 'luis.perez@example.com',
        'institution' => null,
        'phone' => null,
    ]);
});

it('rejects an empty submission and reports the required fields', function () {
    $response = $this->postJson(route('contact.store'), []);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['name', 'email', 'study_type', 'message']);
});

it('rejects an invalid email address', function () {
    $response = $this->postJson(route('contact.store'), [
        'name' => 'Ana Torres',
        'email' => 'not-an-email',
        'study_type' => StudyType::Posttest->value,
        'message' => 'Mensaje de prueba.',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['email']);
});

it('rejects a study type outside the allowed enum values', function () {
    $response = $this->postJson(route('contact.store'), [
        'name' => 'Ana Torres',
        'email' => 'ana.torres@example.com',
        'study_type' => 'not-a-real-type',
        'message' => 'Mensaje de prueba.',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['study_type']);
});

it('rejects a message over the maximum length', function () {
    $response = $this->postJson(route('contact.store'), [
        'name' => 'Ana Torres',
        'email' => 'ana.torres@example.com',
        'study_type' => StudyType::Otro->value,
        'message' => str_repeat('a', 2001),
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['message']);
});

it('does not persist a message when validation fails', function () {
    $this->postJson(route('contact.store'), ['name' => 'Ana Torres']);

    expect(ContactMessage::count())->toBe(0);
});
