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
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->enum('type', [
                'in',          // masuk (pembelian, return-in, transfer-in, adjustment-in)
                'out',         // keluar (penjualan, transfer-out, adjustment-out)
            ]);
            $table->string('source_type');    // Sumber: sales, purchase, return, adjustment, transfer, dll
            $table->unsignedBigInteger('source_id')->nullable(); // id dari sumber (misal sales.id, purchases.id)
            $table->integer('qty');           // +masuk, -keluar (boleh pakai semua positif, tergantung type)
            $table->text('note')->nullable(); // Keterangan/penjelasan
            $table->json('meta')->nullable(); // Metadata opsional (misal: harga, user, dsb)
            $table->timestamp('moved_at')->nullable(); // Tanggal/waktu transaksi (bukan waktu insert)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
