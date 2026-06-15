<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('businesses', function (Blueprint $table) {
            $table->id();
            $table->string('name');                              // Jina la biashara
            $table->decimal('cash_balance', 15, 2)->default(0);  // Tanki la cash
            $table->decimal('lipa_namba_balance', 15, 2)->default(0); // Tanki la Lipa Namba
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('businesses');
    }
};
