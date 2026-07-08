<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('rnh_service_compare_runs')) {
            return;
        }

        if (!Schema::hasColumn('rnh_service_compare_runs', 'ai_input_json_path')) {
            Schema::table('rnh_service_compare_runs', function (Blueprint $table) {
                $table->string('ai_input_json_path')->nullable();
            });
        }

        if (!Schema::hasColumn('rnh_service_compare_runs', 'ai_input_md_path')) {
            Schema::table('rnh_service_compare_runs', function (Blueprint $table) {
                $table->string('ai_input_md_path')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('rnh_service_compare_runs')) {
            return;
        }

        if (Schema::hasColumn('rnh_service_compare_runs', 'ai_input_json_path')) {
            Schema::table('rnh_service_compare_runs', function (Blueprint $table) {
                $table->dropColumn('ai_input_json_path');
            });
        }

        if (Schema::hasColumn('rnh_service_compare_runs', 'ai_input_md_path')) {
            Schema::table('rnh_service_compare_runs', function (Blueprint $table) {
                $table->dropColumn('ai_input_md_path');
            });
        }
    }
};
