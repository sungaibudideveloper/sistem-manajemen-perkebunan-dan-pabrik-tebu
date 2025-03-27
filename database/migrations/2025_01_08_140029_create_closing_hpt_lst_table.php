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
        Schema::create('closing_hpt_lst', function (Blueprint $table) {
            $table->char('no_sample', 4)->default('');
            $table->char('kd_comp', 4)->default('');
            $table->date('tgltanam')->useCurrent();
            $table->integer('no_urut')->default(0);
            $table->integer('jm_batang')->default(0);
            $table->integer('ppt')->default(0);
            $table->integer('pbt')->default(0);
            $table->integer('skor0')->default(0);
            $table->integer('skor1')->default(0);
            $table->integer('skor2')->default(0);
            $table->integer('skor3')->default(0);
            $table->integer('skor4')->default(0);
            $table->decimal('per_ppt', 12,9)->default(0);
            $table->decimal('per_ppt_aktif', 12,9)->default(0);
            $table->decimal('per_pbt', 12,9)->default(0);
            $table->decimal('per_pbt_aktif', 12,9)->default(0);
            $table->integer('sum_ni')->default(0);
            $table->decimal('int_rusak', 12,9)->default(0);
            $table->integer('telur_ppt')->default(0);
            $table->integer('larva_ppt1')->default(0);
            $table->integer('larva_ppt2')->default(0);
            $table->integer('larva_ppt3')->default(0);
            $table->integer('larva_ppt4')->default(0);
            $table->integer('pupa_ppt')->default(0);
            $table->integer('ngengat_ppt')->default(0);
            $table->integer('kosong_ppt')->default(0);
            $table->integer('telur_pbt')->default(0);
            $table->integer('larva_pbt1')->default(0);
            $table->integer('larva_pbt2')->default(0);
            $table->integer('larva_pbt3')->default(0);
            $table->integer('larva_pbt4')->default(0);
            $table->integer('pupa_pbt')->default(0);
            $table->integer('ngengat_pbt')->default(0);
            $table->integer('kosong_pbt')->default(0);
            $table->integer('dh')->default(0);
            $table->integer('dt')->default(0);
            $table->integer('kbp')->default(0);
            $table->integer('kbb')->default(0);
            $table->integer('kp')->default(0);
            $table->integer('cabuk')->default(0);
            $table->integer('belalang')->default(0);
            $table->integer('serang_grayak')->default(0);
            $table->integer('jum_grayak')->default(0);
            $table->integer('serang_smut')->default(0);
            $table->integer('smut_stadia1')->default(0);
            $table->integer('smut_stadia2')->default(0);
            $table->integer('smut_stadia3')->default(0);
            $table->enum('status',['Posted','Unposted'])->default('Unposted');
            $table->integer('count')->default(0);
            $table->string('user_input', 50)->default('');
            $table->timestamps();
            $table->primary(['no_sample', 'kd_comp', 'tgltanam', 'no_urut']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('closing_hpt_lst');
    }
};
