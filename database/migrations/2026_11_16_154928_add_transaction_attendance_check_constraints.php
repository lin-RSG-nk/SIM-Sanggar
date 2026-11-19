<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Jenis item harus sesuai dengan kolom referensinya.
        DB::statement("
            ALTER TABLE transaction_details
            ADD CONSTRAINT chk_transaction_details_item_type
            CHECK (
                (
                    item_type = 'membership'
                    AND membership_package_id IS NOT NULL
                    AND class_rate_id IS NULL
                    AND class_schedule_id IS NULL
                )
                OR
                (
                    item_type = 'pay_per_class'
                    AND membership_package_id IS NULL
                    AND class_rate_id IS NOT NULL
                    AND class_schedule_id IS NOT NULL
                )
            )
        ");

        // Setiap detail transaksi harus memiliki kuantitas minimal 1.
        DB::statement("
            ALTER TABLE transaction_details
            ADD CONSTRAINT chk_transaction_details_quantity
            CHECK (quantity >= 1)
        ");

        // Setiap absensi harus menggunakan tepat satu sumber pembayaran.
        DB::statement("
            ALTER TABLE attendances
            ADD CONSTRAINT chk_attendances_payment_source
            CHECK (
                (
                    membership_id IS NOT NULL
                    AND transaction_detail_id IS NULL
                )
                OR
                (
                    membership_id IS NULL
                    AND transaction_detail_id IS NOT NULL
                )
            )
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE attendances
            DROP CONSTRAINT chk_attendances_payment_source
        ");

        DB::statement("
            ALTER TABLE transaction_details
            DROP CONSTRAINT chk_transaction_details_quantity
        ");

        DB::statement("
            ALTER TABLE transaction_details
            DROP CONSTRAINT chk_transaction_details_item_type
        ");
    }
};
