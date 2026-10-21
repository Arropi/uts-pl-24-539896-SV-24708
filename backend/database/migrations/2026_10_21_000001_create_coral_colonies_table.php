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
        Schema::create('coral_colonies', function (Blueprint $table) {
            $table->id();
            $table->string('kode_fragmen')->unique();
            $table->string('spesies_ilmiah');
            $table->string('zona_terumbu');
            $table->decimal('kedalaman_meter', 5, 2);
            $table->unsignedInteger('persentase_hidup');
            $table->string('status_kesehatan');
            $table->text('catatan_observasi')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coral_colonies');
    }
};
