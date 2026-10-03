<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contents', function (Blueprint $table) {
            $table->id();
            $table->string('page')->index();
            $table->string('section')->index();
            $table->string('key');
            $table->text('value')->nullable();
            $table->string('type')->default('text');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['page', 'section', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contents');
    }
};