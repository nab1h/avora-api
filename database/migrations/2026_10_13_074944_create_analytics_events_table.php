<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analytics_events', function (Blueprint $table) {
            $table->id();

            $table->uuid('visitor_id');
            $table->uuid('session_id');

            $table->string('event_name', 50);
            $table->string('page_path', 2048);
            $table->string('referrer', 2048)->nullable();

            $table->timestamp('created_at')->useCurrent();

            $table->index(['created_at', 'event_name']);
            $table->index(['visitor_id', 'created_at']);
            $table->index(['session_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_events');
    }
};