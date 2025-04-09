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
        Schema::create('plotting', function (Blueprint $table) {
            $table->char('plotcode',5)->default('');
            $table->decimal('luasarea',6,2)->default(0.0);
            $table->decimal('jaraktanam',3, 0)->default(0);
            $table->char('companycode',4)->default('');
            $table->string('usernm',50)->default('');
            $table->timestamps();
            $table->primary(['plotcode', 'companycode']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plotting');
    }
};
