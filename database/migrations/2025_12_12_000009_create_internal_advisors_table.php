<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create table for tracking internal advisor roles.
     * 
     * Allows managers/associates to temporarily take on an advisory role
     * on another team's project. This grants them permission to:
     * - View and comment on the project
     * - Leave reviews on the project
     * 
     * The team_id tracks which team owns the project being advised.
     * Composite unique key prevents duplicate advisor assignments.
     */
    public function up(): void
    {
        Schema::create('internal_advisors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('team_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            $table->unique(['user_id', 'project_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('internal_advisors');
    }
};
