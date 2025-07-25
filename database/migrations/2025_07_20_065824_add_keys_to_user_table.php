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
        Schema::table('users', function (Blueprint $table) {
            $table->string('transaction_key')->nullable();
            $table->boolean('transaction_key_enabled')->default(true);

            $table->string('fiscal_key')->nullable();
            $table->boolean('fiscal_key_enabled')->default(true);

            $table->boolean('keys_locked_by_admin')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user', function (Blueprint $table) {
            $table->dropColumn('transaction_key');
            $table->dropColumn('transaction_key_enabled');
            $table->dropColumn('fiscal_key');
            $table->dropColumn('fiscal_key_enabled');
            $table->dropColumn('keys_locked_by_admin');
        });
    }
};
