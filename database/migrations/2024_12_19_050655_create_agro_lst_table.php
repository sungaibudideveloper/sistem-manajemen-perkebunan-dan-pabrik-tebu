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
        Schema::create('agro_lst', function (Blueprint $table) {
            $table->char('no_sample', 4)->default('');
            $table->char('companycode', 4)->default('');
            $table->date('tanggaltanam')->useCurrent();
            $table->integer('nourut')->default(0);
            $table->integer('jm_batang')->default(0);
            $table->integer('pan_gap')->default(0);
            $table->decimal('per_gap', 7,2)->default(0.0);
            $table->decimal('per_germinasi', 7,2)->default(0.0);
            $table->decimal('ph_tanah', 4,1)->default(0.0);
            $table->decimal('populasi', 7,1)->default(0.0);
            $table->integer('ktk_gulma')->default(0);
            $table->decimal('per_gulma',7,2)->default(0.0);
            $table->integer('t_primer')->default(0);
            $table->integer('t_sekunder')->default(0);
            $table->integer('t_tersier')->default(0);
            $table->integer('t_kuarter')->default(0);
            $table->decimal('d_primer',4,1)->default(0.0);
            $table->decimal('d_sekunder',5,2)->default(0.0);
            $table->decimal('d_tersier',6,3)->default(0.0);
            $table->decimal('d_kuarter',7,4)->default(0.0);
            $table->enum('status',['Posted','Unposted'])->default('Unposted');
            $table->integer('count')->default(0);
            $table->string('inputby', 50)->default('');
            $table->timestamps();
            $table->primary(['no_sample', 'companycode', 'tanggaltanam', 'nourut']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agro_lst');
    }
};
