<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        Schema::create('mapping', function (Blueprint $table) {
            $table->char('plotcodesample', 5)->default('');
            $table->char('blok', 2)->default('');
            $table->char('plotcode', 5)->default('');
            $table->char('companycode', 4)->default('');
            $table->string('usernm', 50)->default('');
            $table->timestamps();
            $table->primary(['plotcodesample', 'blok', 'plotcode', 'companycode']);
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
