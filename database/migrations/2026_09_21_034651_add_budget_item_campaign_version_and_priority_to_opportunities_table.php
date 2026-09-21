<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('opportunities', function (Blueprint $table) {
            $table->string('budget_item')->nullable()->after('title');
            $table->string('campaign')->nullable()->after('budget_item');
            $table->string('version')->nullable()->after('campaign');
            $table->string('priority')->nullable()->after('version');

            $table->index('priority');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('opportunities', function (Blueprint $table) {
            $table->dropIndex(['priority']);
            $table->dropColumn(['budget_item', 'campaign', 'version', 'priority']);
        });
    }
};
