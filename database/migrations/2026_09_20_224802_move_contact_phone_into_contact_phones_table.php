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
     * The single free-text `phone` column becomes a row in `contact_phones`.
     * The old column carried no kind, so existing numbers are stored as mobile.
     */
    public function up(): void
    {
        DB::table('contacts')
            ->whereNotNull('phone')
            ->where('phone', '!=', '')
            ->orderBy('id')
            ->select(['id', 'phone'])
            ->each(function (object $contact) {
                DB::table('contact_phones')->insert([
                    'contact_id' => $contact->id,
                    'type' => 'mobile',
                    'number' => trim($contact->phone),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });

        Schema::table('contacts', function (Blueprint $table) {
            $table->dropColumn('phone');
        });
    }

    /**
     * Reverse the migrations.
     *
     * Only the first phone of each contact fits the single column.
     */
    public function down(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
        });

        DB::table('contact_phones')
            ->orderBy('id')
            ->each(function (object $phone) {
                DB::table('contacts')
                    ->where('id', $phone->contact_id)
                    ->whereNull('phone')
                    ->update(['phone' => $phone->number]);
            });
    }
};
