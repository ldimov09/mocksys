<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('fiscal_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('transaction_id')->nullable();
            $table->foreignId('company_id');
            $table->decimal('total', 10, 2);
            $table->json('items'); // Stores itemized purchase details
            $table->string('transaction_signature')->nullable(); // Card system's approval proof
            $table->enum('status', ['fiscalized', 'error', 'cancelled', 'pending'])->default('fiscalized');
            $table->string('fiscal_signature')->nullable(); // Optional: digital signature for audit
            $table->string('error')->nullable(); 
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fiscal_records');
    }
};
