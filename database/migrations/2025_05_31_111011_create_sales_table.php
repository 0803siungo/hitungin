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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->string('sku');                   // SKU dari marketplace saat penjualan (trace & lookup)
            $table->string('marketplace')->nullable(); // shopee, tokopedia, tiktok, dsb
            $table->string('order_number')->nullable(); // nomor pesanan marketplace
            $table->integer('qty');                    // jumlah unit terjual
            $table->decimal('price', 18, 2);           // harga jual per unit (saat transaksi)
            $table->string('buyer_username')->nullable(); // username pembeli (opsional)
            $table->dateTime('sold_at')->nullable();      // waktu penjualan
            $table->json('raw_data')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
