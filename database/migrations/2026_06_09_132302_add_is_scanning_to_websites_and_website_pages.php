<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('websites', function (Blueprint $table) {
            $table->boolean('is_scanning')->default(false)->after('is_active');
        });

        Schema::table('website_pages', function (Blueprint $table) {
            $table->boolean('is_scanning')->default(false)->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('websites', function (Blueprint $table) {
            $table->dropColumn('is_scanning');
        });

        Schema::table('website_pages', function (Blueprint $table) {
            $table->dropColumn('is_scanning');
        });
    }
};
