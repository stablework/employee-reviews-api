<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create teams table for organizational team structure.
     * 
     * Each team has:
     * - A unique name
     * - One manager (team lead with Manager role)
     * - Multiple members/associates (in team_user pivot table)
     * 
     * Uses RESTRICT on delete for manager_id to prevent orphaned teams
     * if a manager is deleted.
     */
    public function up(): void
    {
        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            // Manager is required for each team; use RESTRICT to prevent orphaning
            $table->foreignId('manager_id')->constrained('users')->onDelete('restrict');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teams');
    }
};
