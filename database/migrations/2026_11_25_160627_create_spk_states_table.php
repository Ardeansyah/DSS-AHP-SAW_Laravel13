<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spk_states', function (Blueprint $table) {
            $table->id();
            $table->boolean('m1_kriteria_valid')->default(false);
            $table->boolean('m2_ahp_konsisten')->default(false);
            $table->decimal('last_cr', 8, 4)->nullable();
            $table->boolean('m3_alternatif_tervalidasi')->default(false);
            $table->boolean('m4_saw_terhitung')->default(false);
            $table->timestamp('m4_calculated_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spk_states');
    }
};