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
        Schema::create('mutations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_location_id');
            $table->date('tanggal');
            $table->enum('jenis_mutasi', ['masuk', 'keluar']);
            $table->integer('jumlah');
            $table->text('keterangan')->nullable();

            $table->timestamps();

            $table->foreign('product_location_id')
                ->references('id')
                ->on('product_locations')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mutations');
    }
};
