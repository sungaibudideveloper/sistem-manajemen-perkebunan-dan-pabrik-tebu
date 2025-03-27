<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   
    public function up(): void
    {
        Schema::create('perusahaan', function (Blueprint $table) {
            $table->char('kd_comp',4)->default('')->primary();
            $table->string('nama',50)->default('');
            $table->text('alamat');
            $table->string('user_input',50)->default('');
            $table->date('tgl')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('perusahaan');
    }
};
