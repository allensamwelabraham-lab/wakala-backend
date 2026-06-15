<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->foreignId('network_id')->nullable()->change();
            $table->decimal('float_after', 15, 2)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->foreignId('network_id')->nullable(false)->change();
            $table->decimal('float_after', 15, 2)->nullable(false)->change();
        });
    }
};
