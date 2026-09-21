<?php

use App\Models\Opportunity;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('opportunities', function (Blueprint $table) {
            $table->unsignedSmallInteger('fiscal_year')->nullable()->after('title');
        });

        DB::table('opportunities')->orderBy('id')->each(function (object $opportunity) {
            $year = $opportunity->created_at ? (int) substr($opportunity->created_at, 0, 4) : Opportunity::FISCAL_YEAR_MIN;

            DB::table('opportunities')->where('id', $opportunity->id)->update([
                'fiscal_year' => min(max($year, Opportunity::FISCAL_YEAR_MIN), Opportunity::FISCAL_YEAR_MAX),
            ]);
        });

        Schema::table('opportunities', function (Blueprint $table) {
            $table->unsignedSmallInteger('fiscal_year')->nullable(false)->change();
            $table->index('fiscal_year');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('opportunities', function (Blueprint $table) {
            $table->dropIndex(['fiscal_year']);
            $table->dropColumn('fiscal_year');
        });
    }
};
