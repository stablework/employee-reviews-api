<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create reviews table for storing all reviews in the system.
     * 
     * Supports two types of reviews:
     * 1. Project Reviews: Reviewee is null, project_id is set
     * 2. Peer Reviews: Project is null, reviewee_id is set
     * 
     * Business rule (enforced in application):
     * - At least one of project_id OR reviewee_id must be non-null
     * - reviewer_id is always required
     * 
     * Timestamps track when reviews were created and last updated
     * for audit trails and sorting.
     */
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            // User who created the review
            $table->foreignId('reviewer_id')->constrained('users')->onDelete('cascade');
            // Project being reviewed (null for peer reviews)
            $table->foreignId('project_id')->nullable()->constrained()->onDelete('cascade');
            // User being reviewed (null for project reviews)
            $table->foreignId('reviewee_id')->nullable()->constrained('users')->onDelete('cascade');
            // Review content/feedback
            $table->text('content');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
