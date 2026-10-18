<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('reference');
            $table->enum('client_type', ['company', 'individual']);
            $table->string('name');
            $table->string('contact_name')->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('email')->nullable();
            $table->boolean('is_blacklisted')->default(false);
            $table->timestamps();

            $table->unique(['user_id', 'reference']);
            $table->index(['user_id', 'is_blacklisted']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
