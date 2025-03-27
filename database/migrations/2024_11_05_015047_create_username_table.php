<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('username', function (Blueprint $table) {
     
            $table->string('usernm', 50)->default('')->primary();
            $table->string('name', 30)->default('');
            $table->string('password', 70)->default('');
            $table->json('permissions')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('username');
    }
};
