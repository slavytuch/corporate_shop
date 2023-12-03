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
        Schema::create('price_type_product', function (Blueprint $table) {
            $table->bigInteger('price_type_id');
            $table->bigInteger('product_id');
            $table->bigInteger('price');

            $table->unique(['price_type_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('price_type_product');
    }
};
