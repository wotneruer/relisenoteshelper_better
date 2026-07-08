<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rnh_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->jsonb('value')->nullable();
            $table->timestamps();
        });

        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->index();
            $table->string('project')->nullable()->index();
            $table->text('git_url')->nullable();
            $table->text('local_path')->nullable();
            $table->string('base_tag')->nullable();
            $table->string('selected_branch')->nullable();
            $table->boolean('created_from_installer')->default(false);
            $table->boolean('needs_git_url')->default(false);
            $table->string('installer_image_name')->nullable();
            $table->string('installer_version')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('validation_status')->nullable()->index();
            $table->text('notes')->nullable();
            $table->jsonb('metadata')->nullable();
            $table->timestampTz('legacy_created_at')->nullable();
            $table->timestamps();
        });

        Schema::create('repositories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->nullable()->constrained('services')->nullOnDelete();
            $table->text('path')->nullable();
            $table->text('remote_url')->nullable();
            $table->string('current_branch')->nullable();
            $table->timestampTz('last_fetch_at')->nullable();
            $table->timestampTz('last_scan_at')->nullable();
            $table->string('status')->nullable();
            $table->text('last_error')->nullable();
            $table->jsonb('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('release_templates', function (Blueprint $table) {
            $table->id();
            $table->string('legacy_id')->nullable()->unique();
            $table->string('name')->index();
            $table->string('project')->nullable()->index();
            $table->text('description')->nullable();
            $table->string('default_release_name')->nullable();
            $table->string('default_target_branch')->nullable();
            $table->string('last_release_version')->nullable();
            $table->string('last_release_name')->nullable();
            $table->string('last_comparison_base_mode')->nullable();
            $table->string('last_diverged_history_diff_mode')->nullable();
            $table->boolean('is_active')->default(true);
            $table->jsonb('settings')->nullable();
            $table->timestampTz('legacy_created_at')->nullable();
            $table->timestampTz('legacy_updated_at')->nullable();
            $table->timestamps();
        });

        Schema::create('release_template_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('release_template_id')->constrained('release_templates')->cascadeOnDelete();
            $table->foreignId('service_id')->nullable()->constrained('services')->nullOnDelete();
            $table->string('service_name')->index();
            $table->text('git_url')->nullable();
            $table->boolean('included')->default(true);
            $table->boolean('ask_if_changed')->default(true);
            $table->string('validation_status')->nullable();
            $table->string('target_branch')->nullable();
            $table->string('baseline_version')->nullable();
            $table->string('baseline_ref')->nullable();
            $table->string('baseline_sha')->nullable();
            $table->text('notes')->nullable();
            $table->integer('order_index')->default(0);
            $table->jsonb('settings')->nullable();
            $table->timestamps();

            $table->unique(['release_template_id', 'service_name'], 'rnh_template_service_unique');
        });

        Schema::create('releases', function (Blueprint $table) {
            $table->id();
            $table->string('legacy_id')->nullable()->index();
            $table->string('name')->index();
            $table->string('version')->nullable();
            $table->string('template_name')->nullable();
            $table->string('project')->nullable()->index();
            $table->string('type')->nullable()->index();
            $table->string('previous_release_legacy_id')->nullable();
            $table->string('source')->nullable();
            $table->text('installer_url')->nullable();
            $table->string('installer_ref')->nullable();
            $table->string('installer_path')->nullable();
            $table->timestampTz('built_at')->nullable();
            $table->timestampTz('scope_generated_at')->nullable();
            $table->string('output_folder_name')->nullable();
            $table->boolean('has_changes')->default(false);
            $table->integer('changed_services_count')->default(0);
            $table->integer('included_services_count')->default(0);
            $table->jsonb('metadata')->nullable();
            $table->timestampTz('legacy_created_at')->nullable();
            $table->timestamps();
        });

        Schema::create('release_baseline_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('release_id')->constrained('releases')->cascadeOnDelete();
            $table->foreignId('service_id')->nullable()->constrained('services')->nullOnDelete();
            $table->string('service_name')->index();
            $table->text('git_url')->nullable();
            $table->boolean('included')->default(true);
            $table->string('base_tag')->nullable();
            $table->string('target_branch')->nullable();
            $table->string('previous_sha')->nullable();
            $table->string('target_ref')->nullable();
            $table->string('target_sha')->nullable();
            $table->string('image_name')->nullable();
            $table->string('source_version')->nullable();
            $table->text('source_image')->nullable();
            $table->text('source_file')->nullable();
            $table->string('status')->nullable();
            $table->text('error_message')->nullable();
            $table->jsonb('metadata')->nullable();
            $table->timestamps();

            $table->unique(['release_id', 'service_name'], 'rnh_release_baseline_service_unique');
        });

        Schema::create('release_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('release_template_id')->nullable()->constrained('release_templates')->nullOnDelete();
            $table->foreignId('release_id')->nullable()->constrained('releases')->nullOnDelete();
            $table->string('legacy_key')->nullable()->unique();
            $table->string('name')->nullable();
            $table->string('status')->default('imported')->index();
            $table->timestampTz('started_at')->nullable();
            $table->timestampTz('finished_at')->nullable();
            $table->jsonb('input')->nullable();
            $table->jsonb('summary')->nullable();
            $table->text('output_path')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
        });

        Schema::create('release_run_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('release_run_id')->constrained('release_runs')->cascadeOnDelete();
            $table->foreignId('service_id')->nullable()->constrained('services')->nullOnDelete();
            $table->string('service_name')->index();
            $table->string('status')->nullable()->index();
            $table->string('from_ref')->nullable();
            $table->string('to_ref')->nullable();
            $table->string('from_sha')->nullable();
            $table->string('to_sha')->nullable();
            $table->string('merge_base_sha')->nullable();
            $table->boolean('is_previous_ancestor_of_target')->nullable();
            $table->boolean('is_target_ancestor_of_previous')->nullable();
            $table->string('effective_diff_base_sha')->nullable();
            $table->string('diff_base_mode')->nullable();
            $table->boolean('used_merge_base_for_diff')->default(false);
            $table->string('diff_range')->nullable();
            $table->integer('commit_count')->default(0);
            $table->integer('jira_count')->default(0);
            $table->integer('changed_file_count')->default(0);
            $table->text('warning')->nullable();
            $table->text('note')->nullable();
            $table->text('error_message')->nullable();
            $table->jsonb('jira_keys')->nullable();
            $table->jsonb('artifacts')->nullable();
            $table->jsonb('metadata')->nullable();
            $table->timestamps();

            $table->unique(['release_run_id', 'service_name'], 'rnh_run_service_unique');
        });

        Schema::create('commits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('release_run_service_id')->constrained('release_run_services')->cascadeOnDelete();
            $table->string('sha')->nullable()->index();
            $table->string('short_sha')->nullable()->index();
            $table->string('author_name')->nullable();
            $table->string('author_email')->nullable();
            $table->timestampTz('committed_at')->nullable();
            $table->text('subject')->nullable();
            $table->text('body')->nullable();
            $table->text('raw')->nullable();
            $table->jsonb('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('changed_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('release_run_service_id')->constrained('release_run_services')->cascadeOnDelete();
            $table->string('status')->nullable()->index();
            $table->text('path')->nullable();
            $table->text('old_path')->nullable();
            $table->text('raw')->nullable();
            $table->jsonb('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('ai_rules', function (Blueprint $table) {
            $table->id();
            $table->string('scope_type')->index();
            $table->string('scope_key')->nullable()->index();
            $table->text('path')->nullable();
            $table->longText('content')->nullable();
            $table->boolean('enabled')->default(true);
            $table->jsonb('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('ai_prompts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('release_run_id')->nullable()->constrained('release_runs')->nullOnDelete();
            $table->foreignId('service_id')->nullable()->constrained('services')->nullOnDelete();
            $table->string('type')->index();
            $table->longText('prompt_text')->nullable();
            $table->boolean('redacted')->default(false);
            $table->jsonb('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('ai_generations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ai_prompt_id')->nullable()->constrained('ai_prompts')->nullOnDelete();
            $table->string('provider')->nullable();
            $table->string('model')->nullable();
            $table->string('status')->default('new')->index();
            $table->longText('response_text')->nullable();
            $table->text('error_message')->nullable();
            $table->jsonb('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_generations');
        Schema::dropIfExists('ai_prompts');
        Schema::dropIfExists('ai_rules');
        Schema::dropIfExists('changed_files');
        Schema::dropIfExists('commits');
        Schema::dropIfExists('release_run_services');
        Schema::dropIfExists('release_runs');
        Schema::dropIfExists('release_baseline_items');
        Schema::dropIfExists('releases');
        Schema::dropIfExists('release_template_services');
        Schema::dropIfExists('release_templates');
        Schema::dropIfExists('repositories');
        Schema::dropIfExists('services');
        Schema::dropIfExists('rnh_settings');
    }
};
