<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seo_pages', function (Blueprint $table) {
            $table->string('locale', 5)
                ->default('en')
                ->after('page');

            $table->dropUnique('seo_pages_page_unique');

            $table->unique(
                ['page', 'locale'],
                'seo_pages_page_locale_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('seo_pages', function (Blueprint $table) {
            $table->dropUnique('seo_pages_page_locale_unique');
            $table->dropColumn('locale');

            $table->unique('page', 'seo_pages_page_unique');
        });
    }
};