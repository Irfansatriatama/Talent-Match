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
        Schema::table('anp_analyses', function (Blueprint $table) {
            if (!Schema::hasColumn('anp_analyses', 'completed_at')) {
                $table->timestamp('completed_at')->nullable()->after('status');
            }
            if (!Schema::hasColumn('anp_analyses', 'calculation_data')) {
                $table->json('calculation_data')->nullable()->after('completed_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('anp_analyses', function (Blueprint $table) {
            $table->dropColumn(['completed_at', 'calculation_data']);
        });
    }
};