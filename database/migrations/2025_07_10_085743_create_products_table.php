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
        Schema::create('products', function (Blueprint $table) {
            $table->uuid()->primary();
            $table->string('kode_produk')->unique();
            $table->string('nama_produk');
            $table->unsignedBigInteger('kategori')->nullable();
            $table->string('satuan')->nullable();
            $table->text('deskripsi')->nullable();
            $table->timestamps();

            $table->foreign('kategori')->references('id')->on('categories')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
