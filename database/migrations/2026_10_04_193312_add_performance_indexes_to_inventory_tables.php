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
        Schema::table('products', function (Blueprint $table) {
            $table->index(['user_id', 'deleted_at'], 'products_owner_archive_index');
            $table->index(['user_id', 'category_id'], 'products_owner_category_index');
            $table->index(['user_id', 'created_at'], 'products_owner_created_index');
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->index(['product_id', 'occurred_at'], 'movements_product_date_index');
            $table->index(['user_id', 'occurred_at'], 'movements_owner_date_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropIndex('movements_product_date_index');
            $table->dropIndex('movements_owner_date_index');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_owner_archive_index');
            $table->dropIndex('products_owner_category_index');
            $table->dropIndex('products_owner_created_index');
        });
    }
};
