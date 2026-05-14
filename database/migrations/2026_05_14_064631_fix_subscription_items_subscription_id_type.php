<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = DB::getDriverName();

        // PostgreSQL production
        if ($driver === 'pgsql') {

            // ลบ foreign key เดิมถ้ามี
            DB::statement("
                DO $$
                BEGIN
                    IF EXISTS (
                        SELECT 1
                        FROM information_schema.table_constraints
                        WHERE constraint_name = 'subscription_items_subscription_id_foreign'
                    ) THEN
                        ALTER TABLE subscription_items
                        DROP CONSTRAINT subscription_items_subscription_id_foreign;
                    END IF;
                END
                $$;
            ");

            // เปลี่ยน type จาก varchar -> bigint
            DB::statement("
                ALTER TABLE subscription_items
                ALTER COLUMN subscription_id TYPE BIGINT
                USING subscription_id::bigint
            ");

            // เพิ่ม foreign key กลับ
            DB::statement("
                ALTER TABLE subscription_items
                ADD CONSTRAINT subscription_items_subscription_id_foreign
                FOREIGN KEY (subscription_id)
                REFERENCES subscriptions(id)
                ON DELETE CASCADE
            ");
        }

        // MySQL local
        elseif ($driver === 'mysql') {
            Schema::table('subscription_items', function (Blueprint $table) {

                // ตรวจสอบก่อนว่าคอลัมน์ยังไม่ถูกต้อง
                try {
                    $table->unsignedBigInteger('subscription_id')->change();
                } catch (\Throwable $e) {
                    // กัน migration fail ถ้า type ถูกอยู่แล้ว
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
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
        }

        elseif ($driver === 'mysql') {
            Schema::table('subscription_items', function (Blueprint $table) {
                try {
                    $table->string('subscription_id')->change();
                } catch (\Throwable $e) {
                    //
                }
            });
        }
    }
};
