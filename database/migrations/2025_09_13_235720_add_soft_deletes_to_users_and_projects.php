<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up(): void
{
    Schema::table('users', function (Blueprint $table) {
        if (!Schema::hasColumn('users', 'deleted_at')) {
            $table->softDeletes();
        }
    });

    Schema::table('projects', function (Blueprint $table) {
        if (!Schema::hasColumn('projects', 'deleted_at')) {
            $table->softDeletes();
        }
    });
}

public function down(): void
{
    Schema::table('users', function (Blueprint $table) {
        if (Schema::hasColumn('users', 'deleted_at')) {
            $table->dropSoftDeletes();
        }
    });

    Schema::table('projects', function (Blueprint $table) {
        if (Schema::hasColumn('projects', 'deleted_at')) {
            $table->dropSoftDeletes();
        }
    });
}
};
