<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scan_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('website_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('website_page_id')
                ->nullable()
                ->constrained('website_pages')
                ->nullOnDelete();

            $table->foreignId('audit_id')
                ->nullable()
                ->constrained('audits')
                ->nullOnDelete();

            $table->string('target_url');

            $table->string('status')->default('running');
            // running, success, failed

            $table->text('error_message')->nullable();

            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scan_logs');
    }
};
