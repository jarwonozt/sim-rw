<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * "villages" merepresentasikan level Kelurahan/Desa.
     */
    public function up(): void
    {
        Schema::create('villages', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->unsignedInteger('subdistrict_id');
            $table->string('name');

            $table->foreign('subdistrict_id')->references('id')->on('subdistricts')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('villages');
    }
};
