<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        Schema::create('notification', function (Blueprint $table) {
            $table->id();
            $table->string('companycode', 255)->default('');
            $table->string('title', 70)->default('');
            $table->text('body');
            $table->string('readby',255)->default('');
            $table->string('inputby',50)->default('');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification');
    }
};
