<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Runs the phone migration against its pre-migration schema, rebuilt by
 * rolling that one migration back inside the test transaction.
 */
function phoneMigration(): object
{
    return require database_path('migrations/2026_09_20_224802_move_contact_phone_into_contact_phones_table.php');
}

function insertContactWithPhone(string $name, ?string $phone): int
{
    $organizationId = DB::table('organizations')->insertGetId([
        'name' => "Org {$name}",
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return DB::table('contacts')->insertGetId([
        'organization_id' => $organizationId,
        'name' => $name,
        'phone' => $phone,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

test('the migration moves existing phones into contact_phones as mobile numbers', function () {
    phoneMigration()->down();

    $withPhone = insertContactWithPhone('Ana', ' 5512345678 ');
    insertContactWithPhone('Luis', null);
    insertContactWithPhone('Rosa', '');

    phoneMigration()->up();

    $phones = DB::table('contact_phones')->get();

    expect($phones)->toHaveCount(1)
        ->and($phones[0]->contact_id)->toBe($withPhone)
        ->and($phones[0]->type)->toBe('mobile')
        ->and($phones[0]->number)->toBe('5512345678')
        ->and(Schema::hasColumn('contacts', 'phone'))->toBeFalse();
});
