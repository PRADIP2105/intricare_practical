<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMergedFieldsToContactsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->unsignedBigInteger('merged_into')->nullable()->after('user_id');
            $table->boolean('is_active')->default(true)->after('merged_into');

            $table->foreign('merged_into')->references('id')->on('contacts')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropForeign(['merged_into']);
            $table->dropColumn('merged_into');
            $table->dropColumn('is_active');
        });
    }
}
