<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        Schema::create('mapping', function (Blueprint $table) {
            $table->char('kd_plotsample', 5)->default('');
            $table->char('kd_blok', 2)->default('');
            $table->char('kd_plot', 5)->default('');
            $table->char('kd_comp', 4)->default('');
            $table->string('usernm', 50)->default('');
            $table->timestamps();
            $table->primary(['kd_plotsample', 'kd_blok', 'kd_plot', 'kd_comp']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mapping');
    }
};
