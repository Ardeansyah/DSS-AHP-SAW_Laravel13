<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('kriterias', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 10)->unique(); // C1, C2, dst.
            $table->string('nama');
            $table->enum('atribut', ['benefit', 'cost'])->default('benefit');
            $table->decimal('bobot', 10, 6)->default(0); // Diisi otomatis oleh AHP
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kriterias');
    }
};