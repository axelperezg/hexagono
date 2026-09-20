<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Runs the sector backfill migration against its pre-migration schema, which
 * is rebuilt by rolling that one migration back inside the test transaction.
 */
function backfillMigration(): object
{
    return require database_path('migrations/2026_09_20_213633_replace_sector_with_sector_id_on_organizations_table.php');
}

function insertOrganizationWithSector(string $name, ?string $sector): void
{
    DB::table('organizations')->insert([
        'name' => $name,
        'sector' => $sector,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

test('the backfill turns free-text sectors into sectors, ignoring case and blanks', function () {
    backfillMigration()->down();

    insertOrganizationWithSector('A', 'Gobierno');
    insertOrganizationWithSector('B', 'gobierno ');
    insertOrganizationWithSector('C', 'Salud');
    insertOrganizationWithSector('D', null);
    insertOrganizationWithSector('E', '  ');

    backfillMigration()->up();

    $sectorIds = DB::table('sectors')->pluck('id', 'name');
    $organizationSectors = DB::table('organizations')->pluck('sector_id', 'name');

    expect($sectorIds->keys()->all())->toEqualCanonicalizing(['Gobierno', 'Salud'])
        ->and($organizationSectors['A'])->toBe($sectorIds['Gobierno'])
        ->and($organizationSectors['B'])->toBe($sectorIds['Gobierno'])
        ->and($organizationSectors['C'])->toBe($sectorIds['Salud'])
        ->and($organizationSectors['D'])->toBeNull()
        ->and($organizationSectors['E'])->toBeNull()
        ->and(Schema::hasColumn('organizations', 'sector'))->toBeFalse();
});

test('the backfill reuses sectors that already exist instead of duplicating them', function () {
    backfillMigration()->down();

    DB::table('sectors')->insert(['name' => 'Gobierno', 'created_at' => now(), 'updated_at' => now()]);
    insertOrganizationWithSector('A', 'Gobierno');
    insertOrganizationWithSector('B', 'gobierno');

    backfillMigration()->up();

    $existingId = DB::table('sectors')->where('name', 'Gobierno')->value('id');

    expect(DB::table('sectors')->count())->toBe(1)
        ->and(DB::table('organizations')->pluck('sector_id')->unique()->all())->toBe([$existingId]);
});
