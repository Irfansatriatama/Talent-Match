<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVersioningToAnpNetworkStructures extends Migration
{
    public function up()
    {
        Schema::table('anp_network_structures', function (Blueprint $table) {
            // Field untuk versioning system
            $table->boolean('is_frozen')->default(false)->after('description');
            $table->unsignedBigInteger('parent_structure_id')->nullable()->after('is_frozen');
            $table->integer('version')->default(1)->after('parent_structure_id');
            $table->timestamp('frozen_at')->nullable()->after('version');
            
            // Foreign key
            $table->foreign('parent_structure_id')
                  ->references('id')
                  ->on('anp_network_structures')
                  ->onDelete('SET NULL');
            
            // Index untuk performa
            $table->index('is_frozen');
            $table->index('parent_structure_id');
        });
    }

    public function down()
    {
        Schema::table('anp_network_structures', function (Blueprint $table) {
            $table->dropForeign(['parent_structure_id']);
            $table->dropColumn(['is_frozen', 'parent_structure_id', 'version', 'frozen_at']);
        });
    }
}