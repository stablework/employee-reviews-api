<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create roles table for role-based access control.
     * 
     * Roles define permissions in the system:
     * - Executive: Full system access, can see all reviews with reviewer names
     * - Manager: Can manage their team and view team-related reviews
     * - Associate: Can view team projects and leave reviews
     * - Internal Advisor: Can review assigned projects
     */
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
