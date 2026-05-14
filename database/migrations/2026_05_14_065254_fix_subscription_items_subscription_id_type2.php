<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {

            DB::statement("
                ALTER TABLE subscription_items
                DROP CONSTRAINT IF EXISTS subscription_items_subscription_id_foreign
            ");

            DB::statement("
                ALTER TABLE subscription_items
                ALTER COLUMN subscription_id TYPE VARCHAR(255)
                USING subscription_id::varchar
            ");

            DB::statement("
                ALTER TABLE subscription_items
                ADD CONSTRAINT subscription_items_subscription_id_foreign
                FOREIGN KEY (subscription_id)
                REFERENCES subscriptions(id)
                ON DELETE CASCADE
            ");
        }

        elseif ($driver === 'mysql') {

            // ลบ foreign key เดิม
            $foreignKeys = DB::select("
                SELECT CONSTRAINT_NAME
                FROM information_schema.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = 'subscription_items'
                AND COLUMN_NAME = 'subscription_id'
                AND REFERENCED_TABLE_NAME IS NOT NULL
            ");

            Schema::table('subscription_items', function (Blueprint $table) use ($foreignKeys) {
                foreach ($foreignKeys as $fk) {
                    try {
                        $table->dropForeign($fk->CONSTRAINT_NAME);
                    } catch (\Throwable $e) {}
                }
            });

            // ตรวจ type ของ subscriptions.id
            $column = DB::selectOne("
                SELECT DATA_TYPE
                FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = 'subscriptions'
                AND COLUMN_NAME = 'id'
            ");

            Schema::table('subscription_items', function (Blueprint $table) use ($column) {

                if (in_array($column->DATA_TYPE, ['bigint', 'int', 'integer'])) {
                    $table->unsignedBigInteger('subscription_id')->change();
                } else {
                    $table->string('subscription_id')->change();
                }
            });

            // เพิ่ม FK ใหม่
            Schema::table('subscription_items', function (Blueprint $table) {
                $table->foreign('subscription_id')
                    ->references('id')
                    ->on('subscriptions')
                    ->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        //
    }
};