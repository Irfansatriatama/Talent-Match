<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSoftDeletesToAnpTables extends Migration
{
    public function up()
    {
        Schema::table('anp_clusters', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('anp_elements', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('anp_dependencies', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::table('anp_clusters', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('anp_elements', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('anp_dependencies', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
}