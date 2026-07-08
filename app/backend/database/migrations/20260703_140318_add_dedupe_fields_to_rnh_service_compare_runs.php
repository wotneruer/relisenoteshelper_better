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

        Schema::table('rnh_service_compare_runs', function (Blueprint $table) {
            if (!Schema::hasColumn('rnh_service_compare_runs', 'compare_hash')) {
                $table->string('compare_hash', 64)->nullable()->index();
            }

            if (!Schema::hasColumn('rnh_service_compare_runs', 'request_count')) {
                $table->unsignedInteger('request_count')->default(1);
            }

            if (!Schema::hasColumn('rnh_service_compare_runs', 'last_used_at')) {
                $table->timestamp('last_used_at')->nullable()->index();
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('rnh_service_compare_runs')) {
            return;
        }

        Schema::table('rnh_service_compare_runs', function (Blueprint $table) {
            if (Schema::hasColumn('rnh_service_compare_runs', 'last_used_at')) {
                $table->dropColumn('last_used_at');
            }

            if (Schema::hasColumn('rnh_service_compare_runs', 'request_count')) {
                $table->dropColumn('request_count');
            }

            if (Schema::hasColumn('rnh_service_compare_runs', 'compare_hash')) {
                $table->dropColumn('compare_hash');
            }
        });
    }
};
