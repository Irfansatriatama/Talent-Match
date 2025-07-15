<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyOnDeleteCascadeForElementsTable extends Migration
{
    public function up()
    {
        Schema::table('anp_elements', function (Blueprint $table) {
            // Hapus foreign key lama
            $table->dropForeign(['anp_cluster_id']);
            
            // Tambahkan foreign key baru dengan onDelete('cascade')
            $table->foreign('anp_cluster_id')
                  ->references('id')
                  ->on('anp_clusters')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('anp_elements', function (Blueprint $table) {
            $table->dropForeign(['anp_cluster_id']);
            $table->foreign('anp_cluster_id')
                  ->references('id')
                  ->on('anp_clusters')
                  ->onDelete('set null');
        });
    }
}