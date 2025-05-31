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
        Schema::create('returns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->nullable()->constrained('sales')->nullOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('sku');
            $table->integer('qty');
            $table->decimal('price', 18, 2)->nullable();
            $table->string('marketplace')->nullable();
            $table->string('order_number')->nullable();
            $table->string('buyer_username')->nullable();
            $table->string('reason')->nullable();
            $table->string('status')->default('completed'); // completed, pending, rejected, etc.
            $table->text('note')->nullable();
            $table->json('raw_data')->nullable();
            $table->timestamp('returned_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('returns');
    }
};
