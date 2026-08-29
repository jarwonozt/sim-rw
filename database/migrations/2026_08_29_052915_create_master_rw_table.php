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
        Schema::create('master_rw', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('village_id');
            $table->string('nomor_rw');
            $table->foreignId('ketua_rw_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('address')->nullable();
            $table->timestamps();

            $table->foreign('village_id')->references('id')->on('villages')->restrictOnDelete();
            $table->unique(['village_id', 'nomor_rw']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_rw');
    }
};
