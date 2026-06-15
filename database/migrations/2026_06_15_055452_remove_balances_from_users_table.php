<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['cash_balance', 'lipa_namba_balance']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('cash_balance', 15, 2)->default(0);
            $table->decimal('lipa_namba_balance', 15, 2)->default(0);
        });
    }
};
