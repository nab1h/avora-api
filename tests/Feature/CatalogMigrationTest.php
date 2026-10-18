<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CatalogMigrationTest extends TestCase
{
    public function test_additive_migrations_and_rollback_preserve_unassigned_legacy_images(): void
    {
        (require database_path('migrations/2026_09_21_114612_create_gallery_items_table.php'))->up();
        $id = DB::table('gallery_items')->insertGetId(['image' => 'gallery/existing.png']);
        $migrations = [];
        foreach (glob(database_path('migrations/2026_10_18_*.php')) as $file) {
            $migration = require $file;
            $migration->up();
            $migrations[] = $migration;
        }
        $this->assertSame('gallery/existing.png', DB::table('gallery_items')->where('id', $id)->value('image'));
        $this->assertNull(DB::table('gallery_items')->where('id', $id)->value('gallery_id'));
        foreach (['galleries', 'categories', 'products', 'images'] as $table) {
            $this->assertTrue(Schema::hasTable($table));
        }
        foreach (array_reverse($migrations) as $migration) {
            $migration->down();
        }
        $this->assertFalse(Schema::hasColumn('gallery_items', 'gallery_id'));
        $this->assertSame('gallery/existing.png', DB::table('gallery_items')->where('id', $id)->value('image'));
    }
}
