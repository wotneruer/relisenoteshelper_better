<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('rnh_service_compare_runs')) {
            return;
        }

        Schema::create('rnh_service_compare_runs', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('service_id')->index();
            $table->string('service_name')->nullable();
            $table->string('project')->nullable();

            $table->string('status', 32)->default('ok')->index();
            $table->string('scenario')->nullable();

            $table->string('base_ref_type', 32)->nullable();
            $table->string('base_ref_name')->nullable();
            $table->string('base_ref_sha', 64)->nullable();
            $table->string('base_ref_source', 64)->nullable();

            $table->string('target_ref_type', 32)->nullable();
            $table->string('target_ref_name')->nullable();
            $table->string('target_ref_sha', 64)->nullable();
            $table->string('target_ref_source', 64)->nullable();

            $table->unsignedInteger('commit_count')->default(0);
            $table->unsignedInteger('file_count')->default(0);
            $table->text('shortstat')->nullable();

            $table->json('request_payload')->nullable();
            $table->json('summary')->nullable();
            $table->json('commits')->nullable();
            $table->json('files')->nullable();
            $table->json('result_payload')->nullable();

            $table->string('repo_path')->nullable();

            $table->timestamps();

            $table->index(['service_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rnh_service_compare_runs');
    }
};
