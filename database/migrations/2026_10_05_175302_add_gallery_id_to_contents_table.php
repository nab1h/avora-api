<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('contents', 'gallery_id')) {
            Schema::table('contents', function (Blueprint $table) {
                $table->foreignId('gallery_id')
                    ->nullable()
                    ->constrained('gallery_items')
                    ->nullOnDelete();
            });

            return;
        }

        $hasGalleryForeignKey = collect(Schema::getForeignKeys('contents'))
            ->contains(fn (array $foreignKey): bool =>
                $foreignKey['columns'] === ['gallery_id']
                && $foreignKey['foreign_table'] === 'gallery_items'
            );

        if (! $hasGalleryForeignKey) {
            Schema::table('contents', function (Blueprint $table) {
                $table->foreign('gallery_id')
                    ->references('id')
                    ->on('gallery_items')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('contents', 'gallery_id')) {
            Schema::table('contents', function (Blueprint $table) {
                $table->dropConstrainedForeignId('gallery_id');
            });
        }
    }
};