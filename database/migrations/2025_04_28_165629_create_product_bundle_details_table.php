<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('product_bundle_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bundle_product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('component_product_id')->constrained('products')->onDelete('cascade');
            $table->integer('qty')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_bundle_details');
    }
};
