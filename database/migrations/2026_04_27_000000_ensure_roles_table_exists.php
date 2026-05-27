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
        if (!Schema::hasTable('roles')) {
            Schema::create('roles', function (Blueprint $table) {
                $table->id();
                $table->string('role_name')->unique();
                $table->string('display_name');
                $table->text('role_description')->nullable();
                $table->timestamps();
            });
        } else {
            Schema::table('roles', function (Blueprint $table) {
                if (!Schema::hasColumn('roles', 'role_name')) {
                    $table->string('role_name')->nullable()->after('id');
                }
                if (!Schema::hasColumn('roles', 'display_name')) {
                    $table->string('display_name')->nullable()->after('role_name');
                }
                if (!Schema::hasColumn('roles', 'role_description')) {
                    $table->text('role_description')->nullable()->after('display_name');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // We don't drop the table here because it might be a shared table
        // However, we could remove the columns we added if they were added via this migration
        // For simplicity and safety in a "ensure" migration, we leave it as is.
    }
};
