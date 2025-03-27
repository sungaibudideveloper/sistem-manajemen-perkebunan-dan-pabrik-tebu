<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        Schema::create('blok', function (Blueprint $table) {
            $table->char('kd_blok',2)->default('');
            $table->char('kd_comp',4)->default('');
            $table->string('usernm',50)->default('');
            $table->timestamps();
            $table->primary(['kd_blok', 'kd_comp']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blok');
    }
};
