<?php

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
        Schema::table('tasks', function (Blueprint $table) {
            $table->renameColumn('title', 'concept');
            $table->renameColumn('due_date', 'end_date');
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->renameIndex('tasks_due_date_index', 'tasks_end_date_index');
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->date('start_date')->nullable()->after('concept');
            $table->foreignId('opportunity_id')->nullable()->change();
        });

        DB::table('tasks')->update(['start_date' => DB::raw('DATE(created_at)')]);

        Schema::table('tasks', function (Blueprint $table) {
            $table->date('start_date')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('tasks')->whereNull('opportunity_id')->delete();

        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn('start_date');
            $table->foreignId('opportunity_id')->nullable(false)->change();
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->renameIndex('tasks_end_date_index', 'tasks_due_date_index');
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->renameColumn('end_date', 'due_date');
            $table->renameColumn('concept', 'title');
        });
    }
};
