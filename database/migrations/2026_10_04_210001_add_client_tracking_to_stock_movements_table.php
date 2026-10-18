<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->foreignId('client_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
            $table->boolean('is_return')->default(false)->after('type');
            $table->index(['client_id', 'is_return']);
        });
    }

    public function down(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropIndex(['client_id', 'is_return']);
            $table->dropConstrainedForeignId('client_id');
            $table->dropColumn('is_return');
        });
    }
};
