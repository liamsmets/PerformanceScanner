<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audits', function (Blueprint $table) {
            $table->unsignedInteger('runs_used')->nullable()->after('website_id');
            $table->unsignedInteger('lcp_ms')->nullable()->after('seo_score');
            $table->unsignedInteger('fcp_ms')->nullable()->after('lcp_ms');
            $table->unsignedInteger('tbt_ms')->nullable()->after('fcp_ms');
            $table->decimal('cls', 8, 3)->nullable()->after('tbt_ms');

            $table->index(['website_id', 'scanned_at']);
        });
    }

    public function down(): void
    {
        Schema::table('audits', function (Blueprint $table) {
            $table->dropIndex(['website_id', 'scanned_at']);

            $table->dropColumn([
                'runs_used',
                'lcp_ms',
                'fcp_ms',
                'tbt_ms',
                'cls',
            ]);
        });
    }
};
