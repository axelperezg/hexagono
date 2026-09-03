<?php

use App\Enums\StudyType;
use App\Livewire\ContactMessages\Index;
use App\Models\ContactMessage;
use App\Models\User;
use Livewire\Livewire;

test('the inbox requires authentication', function () {
    $response = $this->get(route('contact-messages.index'));

    $response->assertRedirect(route('login'));
});

test('the inbox is displayed to an authenticated user', function () {
    $this->actingAs(User::factory()->create());

    $this->get(route('contact-messages.index'))->assertOk();
});

test('it lists contact messages with the newest first', function () {
    $this->actingAs(User::factory()->create());

    $older = ContactMessage::factory()->create(['name' => 'Ana Torres', 'created_at' => now()->subDay()]);
    $newer = ContactMessage::factory()->create(['name' => 'Luis Pérez', 'created_at' => now()]);

    Livewire::test(Index::class)
        ->assertSeeInOrder([$newer->name, $older->name]);
});

test('it filters messages by search term', function () {
    $this->actingAs(User::factory()->create());

    // Institution is pinned (rather than left to the factory's random
    // faker->company()) so it can never accidentally contain the search
    // term itself, which the query also matches against — see
    // App\Livewire\ContactMessages\Index::messages().
    ContactMessage::factory()->create(['name' => 'Ana Torres', 'email' => 'ana@example.com', 'institution' => 'Secretaría de Ejemplo']);
    ContactMessage::factory()->create(['name' => 'Luis Pérez', 'email' => 'luis@example.com', 'institution' => 'Despacho Ríos']);

    Livewire::test(Index::class)
        ->set('search', 'ana')
        ->assertSee('Ana Torres')
        ->assertDontSee('Luis Pérez');
});

test('it filters messages by study type', function () {
    $this->actingAs(User::factory()->create());

    ContactMessage::factory()->create(['name' => 'Ana Torres', 'study_type' => StudyType::Pretest]);
    ContactMessage::factory()->create(['name' => 'Luis Pérez', 'study_type' => StudyType::Opinion]);

    Livewire::test(Index::class)
        ->set('studyType', StudyType::Opinion->value)
        ->assertSee('Luis Pérez')
        ->assertDontSee('Ana Torres');
});

test('opening a message selects it for the detail modal', function () {
    $this->actingAs(User::factory()->create());

    $message = ContactMessage::factory()->create();

    Livewire::test(Index::class)
        ->call('show', $message->id)
        ->assertSet('selectedMessageId', $message->id)
        ->assertSee($message->message);
});
