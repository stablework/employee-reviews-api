<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create pivot table for many-to-many relationship between projects and teams.
     * 
     * Allows multiple teams to collaborate on the same project
     * and a team to work on multiple projects.
     * 
     * Composite unique key prevents duplicate project-team assignments.
     */
    public function up(): void
    {
        Schema::create('project_team', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('team_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            $table->unique(['project_id', 'team_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_team');
    }
};
