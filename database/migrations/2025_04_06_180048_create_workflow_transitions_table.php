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
        Schema::create('workflow_transitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_id')->constrained()->onDelete('cascade');
            $table->foreignId('from_stage_id')->constrained('workflow_stages')->onDelete('cascade');
            $table->foreignId('to_stage_id')->constrained('workflow_stages')->onDelete('cascade');
            $table->json('condition')->nullable();
            $table->string('trigger_type')->default('manual'); // manual, automatic, scheduled
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workflow_transitions');
    }
};
