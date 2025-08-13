<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('description');
            $table->string('address')->nullable();
            $table->string('number'); // user-defined identifier
            $table->string('device_key', 64)->unique(); // HMAC secret
            $table->enum('status', ['enabled', 'disabled'])->default('enabled');
            $table->ipAddress('last_ip')->nullable();
            $table->timestamp('last_seen')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};
