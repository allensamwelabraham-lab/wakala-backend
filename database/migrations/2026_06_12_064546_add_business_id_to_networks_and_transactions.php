<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('networks', function (Blueprint $table) {
            $table->foreignId('business_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->foreignId('business_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
            // Nani alirekodi (kwa uwajibikaji)
            $table->foreignId('recorded_by')->nullable()->after('business_id')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('networks', function (Blueprint $table) {
            $table->dropForeign(['business_id']);
            $table->dropColumn('business_id');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['business_id']);
            $table->dropForeign(['recorded_by']);
            $table->dropColumn(['business_id', 'recorded_by']);
        });
    }
};
