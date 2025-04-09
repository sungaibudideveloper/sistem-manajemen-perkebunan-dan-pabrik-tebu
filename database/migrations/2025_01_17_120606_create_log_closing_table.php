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
        Schema::create('log_closing', function (Blueprint $table) {
            $table->char('companycode',4)->default('');
            $table->date('tgl1')->useCurrent();
            $table->date('tgl2')->useCurrent();
            $table->primary(['companycode', 'tgl1', 'tgl2']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('log_closing');
    }
};
