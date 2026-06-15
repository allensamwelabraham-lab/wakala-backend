<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Biashara ambayo mtumiaji ni wa kwake
            $table->foreignId('business_id')->nullable()->after('id')->constrained()->nullOnDelete();
            // Jukumu: boss au mfanyakazi
            $table->string('role')->default('mfanyakazi')->after('email');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['business_id']);
            $table->dropColumn(['business_id', 'role']);
        });
    }
};
