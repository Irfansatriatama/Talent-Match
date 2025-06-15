<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Step 1: Update existing 'hr' values to temporary value
        DB::statement("UPDATE users SET role = 'candidate' WHERE role = 'hr'");
        
        // Step 2: Alter enum to include 'HR' (uppercase)
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('candidate', 'HR') NOT NULL DEFAULT 'candidate'");
        
        // Step 3: Update back from temporary value to 'HR'
        // First, we need to identify which users were HR
        // This assumes you have another way to identify HR users
        // Option A: If you remember the HR user IDs
        // DB::statement("UPDATE users SET role = 'HR' WHERE id IN (1, 2, 3)");
        
        // Option B: If HR users have specific email patterns
        // DB::statement("UPDATE users SET role = 'HR' WHERE email LIKE '%@company.com'");
        
        // Option C: Manual update after migration
        // You'll need to manually update HR users after migration
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Step 1: Update 'HR' back to 'hr' temporarily
        DB::statement("UPDATE users SET role = 'candidate' WHERE role = 'HR'");
        
        // Step 2: Revert enum back to lowercase
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('candidate', 'hr') NOT NULL DEFAULT 'candidate'");
        
        // Step 3: Restore HR users (same logic as up() method)
    }
};