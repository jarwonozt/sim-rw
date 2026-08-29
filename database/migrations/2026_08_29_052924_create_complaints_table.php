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
        Schema::create('complaints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('rt_id')->constrained('master_rt')->restrictOnDelete();
            $table->string('title');
            $table->text('description');
            $table->string('photo')->nullable();
            $table->enum('status', [
                'menunggu_verifikasi_rt',
                'diteruskan_rw',
                'proses',
                'selesai',
            ])->default('menunggu_verifikasi_rt');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('complaints');
    }
};
