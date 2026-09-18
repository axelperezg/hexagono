<?php

test('returns a successful response', function () {
    $response = $this->get(route('home'));

    $response->assertOk();
});

test('landing page shows the commercial contact email and no phone number', function () {
    $response = $this->get(route('home'));

    $response->assertSee('comercial@hexagono-ci.com');
    $response->assertDontSee('+52 (55) 0000 0000');
});
