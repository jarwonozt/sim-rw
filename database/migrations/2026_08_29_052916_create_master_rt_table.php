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
        Schema::create('master_rt', function (Blueprint $table) {
            $table->id();
            $table->foreignId('master_rw_id')->constrained('master_rw')->cascadeOnDelete();
            $table->string('nomor_rt');
            $table->foreignId('ketua_rt_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['master_rw_id', 'nomor_rt']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_rt');
    }
};
