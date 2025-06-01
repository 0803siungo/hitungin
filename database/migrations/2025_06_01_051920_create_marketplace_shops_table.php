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
        Schema::create('marketplace_shops', function (Blueprint $table) {
            $table->id();
            $table->string('platform'); // Shopee, Tokopedia, dsb
            $table->string('shop_id')->nullable(); // dari Shopee
            $table->string('shop_name');
            $table->string('username')->nullable();
            $table->string('region')->nullable();
            $table->string('access_token')->nullable();
            $table->string('refresh_token')->nullable();
            $table->timestamp('token_expired_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('marketplace_shops');
    }
};
