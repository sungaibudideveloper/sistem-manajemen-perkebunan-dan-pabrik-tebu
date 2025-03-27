<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('hpt_hdr', function (Blueprint $table) {
            $table->char('no_sample', 4)->default('');
            $table->char('kd_comp', 4)->default('');
            $table->char('kd_blok', 2)->default('');
            $table->char('kd_plot', 5)->default('');
            $table->char('kd_plotsample', 5)->default('');
            $table->string('varietas', 10)->default('');
            $table->date('tgltanam')->useCurrent();
            $table->date('tglamat')->useCurrent();
            $table->enum('status',['Posted','Unposted'])->default('Unposted');
            $table->integer('count')->default(0);
            $table->string('user_input', 50)->default('');
            $table->timestamps();
            $table->primary(['no_sample', 'kd_comp', 'tgltanam']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hpt_hdr');
    }
};
