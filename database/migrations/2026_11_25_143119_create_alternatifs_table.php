<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('alternatifs', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 15)->unique(); // A1, A2, dst.
            $table->string('nama');
            $table->string('kelas', 50)->nullable();
            $table->text('ket_c3')->nullable(); // Catatan sertifikat akademik
            $table->text('ket_c4')->nullable(); // Catatan sertifikat non-akademik
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alternatifs');
    }
};