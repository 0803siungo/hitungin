<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('stock_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->date('transaction_date');
            $table->string('transaction_type'); // sale, purchase, cancel_sale, adjustment_in, adjustment_out
            $table->foreignId('reference_id')->nullable(); // bisa dari sales, purchase, dll
            $table->string('reference_type')->nullable(); // nama model: Sale, Purchase
            $table->integer('qty'); // + = masuk, - = keluar
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_transactions');
    }
};
