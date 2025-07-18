<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAnpStructureSnapshotsTable extends Migration
{
    public function up()
    {
        Schema::create('anp_structure_snapshots', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('anp_analysis_id');
            $table->unsignedBigInteger('anp_network_structure_id');
            $table->json('snapshot_data'); // Menyimpan full struktur saat itu
            $table->string('snapshot_type'); // 'proceed_to_comparison', 'manual', etc
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->foreign('anp_analysis_id')->references('id')->on('anp_analyses')->onDelete('cascade');
            $table->foreign('anp_network_structure_id')->references('id')->on('anp_network_structures')->onDelete('cascade');
            
            $table->index(['anp_analysis_id', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('anp_structure_snapshots');
    }
}