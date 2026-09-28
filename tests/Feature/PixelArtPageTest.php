<?php

use App\Enums\StudyType;

it('renders the pixel art landing page with the same content as the home page', function () {
    $response = $this->get(route('pixelart'));

    $response->assertOk();
    $response->assertSeeInOrder([
        'Evidencia rigurosa para decisiones que importan.',
        'Estudios pre-test de campañas',
        'Estudios post-test / evaluación de impacto',
        'Investigación de opinión pública',
        'Inteligencia de audiencias',
        'Análisis e inteligencia digital',
        'Consultoría en comunicación social y análisis de datos',
        'Un proceso trazable, de principio a fin.',
        'Un interlocutor técnico para el sector público.',
        'Solicita información sobre tu estudio.',
    ]);
    $response->assertSee('comercial@hexagono-ci.com');
});

it('explains each service with a labelled diagram', function () {
    $response = $this->get(route('pixelart'));

    $response->assertSee('<title>Las piezas de la campaña se prueban con la audiencia objetivo', escape: false);
    $response->assertSee('VEREDICTO: ¿ESTÁ LISTA PARA SALIR?');
    $response->assertSee('CLIMA DIGITAL');
    $response->assertSee('ACOMPAÑAMIENTO CONTINUO');
});

it('includes the working contact form with its anti-spam fields', function () {
    $response = $this->get(route('pixelart'));

    $response->assertSee(route('contact.store'), escape: false);
    $response->assertSee('name="website"', escape: false);
    $response->assertSee('name="rendered_at"', escape: false);
    $response->assertSee(StudyType::Pretest->label());
});
