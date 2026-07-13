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
        Schema::table('reservation_requests', function (Blueprint $table): void {
            $table->timestamp('completed_at')->nullable()->after('expires_at');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->unique('reservation_request_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservation_requests', function (Blueprint $table): void {
            $table->dropColumn('completed_at');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropUnique(['reservation_request_id']);
        });
    }
};
