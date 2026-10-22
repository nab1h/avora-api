
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('specifications', function (Blueprint $table) {
            $table->id();
            $table->string('spec_key', 100)->unique();
            $table->string('name_ar');
            $table->string('name_en');
            $table->string('unit', 50)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            $table->index(['status', 'sort_order', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('specifications');
    }
};
