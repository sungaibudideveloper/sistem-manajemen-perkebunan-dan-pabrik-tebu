<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usercomp', function (Blueprint $table) {
            $table->string('usernm', 50)->default('');
            $table->string('kd_comp',255);
            $table->string('user_input', 50)->default('');
            $table->timestamps();
            $table->primary(['usernm', 'kd_comp']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usercomp');
    }
};
