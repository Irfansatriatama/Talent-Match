<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('anp_analyses', function (Blueprint $table) {
            // Make anp_network_structure_id nullable
            $table->unsignedBigInteger('anp_network_structure_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('anp_analyses', function (Blueprint $table) {
            // Revert to not nullable (careful: this will fail if there are NULL values)
            $table->unsignedBigInteger('anp_network_structure_id')->nullable(false)->change();
        });
    }
};