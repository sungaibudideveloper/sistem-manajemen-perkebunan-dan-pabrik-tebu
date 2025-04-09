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
            $table->string('companycode',255);
            $table->string('inputby', 50)->default('');
            $table->timestamps();
            $table->primary(['usernm', 'companycode']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usercomp');
    }
};
