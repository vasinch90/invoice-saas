<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE subscription_items ALTER COLUMN subscription_id TYPE VARCHAR(255) USING subscription_id::VARCHAR');
        } else {
            Schema::table('subscription_items', function (Blueprint $table) {
                $table->string('subscription_id')->change();
            });
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE subscription_items ALTER COLUMN subscription_id TYPE BIGINT USING subscription_id::BIGINT');
        } else {
            Schema::table('subscription_items', function (Blueprint $table) {
                $table->unsignedBigInteger('subscription_id')->change();
            });
        }
    }
};
