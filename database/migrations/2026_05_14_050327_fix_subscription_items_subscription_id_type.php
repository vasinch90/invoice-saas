<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE subscription_items ALTER COLUMN subscription_id TYPE VARCHAR(255) USING subscription_id::VARCHAR');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE subscription_items ALTER COLUMN subscription_id TYPE BIGINT USING subscription_id::BIGINT');
    }
};