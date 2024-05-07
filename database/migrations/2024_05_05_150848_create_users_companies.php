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
        Schema::create('users_companies', function (Blueprint $table) {
            $table->foreignId('user_id')
                ->constrained(table: 'users')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('company_id')
                ->constrained(table: 'companies')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users_companies');
    }
};
