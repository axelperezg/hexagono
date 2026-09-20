<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * The free-text `sector` column becomes a foreign key: every distinct
     * value already stored (case-insensitively) is turned into a sector.
     */
    public function up(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->foreignId('sector_id')->nullable()->after('name')->constrained()->restrictOnDelete();
        });

        $sectorIds = [];

        DB::table('organizations')
            ->whereNotNull('sector')
            ->orderBy('id')
            ->select(['id', 'sector'])
            ->each(function (object $organization) use (&$sectorIds) {
                $name = trim($organization->sector);

                if ($name === '') {
                    return;
                }

                $key = mb_strtolower($name);

                $sectorIds[$key] ??= DB::table('sectors')->insertGetId([
                    'name' => $name,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('organizations')->where('id', $organization->id)->update(['sector_id' => $sectorIds[$key]]);
            });

        Schema::table('organizations', function (Blueprint $table) {
            $table->dropColumn('sector');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->string('sector')->nullable()->after('name');
        });

        DB::table('organizations')
            ->whereNotNull('sector_id')
            ->orderBy('id')
            ->select(['id', 'sector_id'])
            ->each(function (object $organization) {
                DB::table('organizations')->where('id', $organization->id)->update([
                    'sector' => DB::table('sectors')->where('id', $organization->sector_id)->value('name'),
                ]);
            });

        Schema::table('organizations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('sector_id');
        });
    }
};
