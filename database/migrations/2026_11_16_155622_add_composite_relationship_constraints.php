
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        /*
         * 1. Membuat unique index gabungan pada tabel induk.
         * Index ini menjadi target composite foreign key.
         */

        DB::statement("
            ALTER TABLE transactions
            ADD UNIQUE KEY uq_transactions_id_member (id, member_id)
        ");

        DB::statement("
            ALTER TABLE transaction_details
            ADD UNIQUE KEY uq_transaction_details_relation
            (id, transaction_id, membership_package_id)
        ");

        DB::statement("
            ALTER TABLE memberships
            ADD UNIQUE KEY uq_memberships_id_member (id, member_id)
        ");

        DB::statement("
            ALTER TABLE attendances
            ADD UNIQUE KEY uq_attendances_id_membership
            (id, membership_id)
        ");

        /*
         * 2. Membership harus terhubung dengan transaksi
         * yang dimiliki member yang sama.
         */

        DB::statement("
            ALTER TABLE memberships
            ADD CONSTRAINT fk_memberships_transaction_member
            FOREIGN KEY (transaction_id, member_id)
            REFERENCES transactions (id, member_id)
            ON DELETE RESTRICT
            ON UPDATE RESTRICT
        ");

        /*
         * 3. Detail transaksi harus berasal dari transaksi
         * yang sama dan menggunakan paket yang sama.
         */

        DB::statement("
            ALTER TABLE memberships
            ADD CONSTRAINT fk_memberships_detail_transaction_package
            FOREIGN KEY (
                transaction_detail_id,
                transaction_id,
                membership_package_id
            )
            REFERENCES transaction_details (
                id,
                transaction_id,
                membership_package_id
            )
            ON DELETE RESTRICT
            ON UPDATE RESTRICT
        ");

        /*
         * 4. transaction_id dan transaction_detail_id
         * harus terisi bersama atau sama-sama NULL.
         */

        DB::statement("
            ALTER TABLE memberships
            ADD CONSTRAINT chk_memberships_transaction_detail_pair
            CHECK (
                (
                    transaction_id IS NULL
                    AND transaction_detail_id IS NULL
                )
                OR
                (
                    transaction_id IS NOT NULL
                    AND transaction_detail_id IS NOT NULL
                )
            )
        ");

        /*
         * 5. Membership pada absensi harus dimiliki
         * oleh member yang melakukan absensi.
         */

        DB::statement("
            ALTER TABLE attendances
            ADD CONSTRAINT fk_attendance_membership_owner
            FOREIGN KEY (membership_id, member_id)
            REFERENCES memberships (id, member_id)
            ON DELETE RESTRICT
            ON UPDATE RESTRICT
        ");

        /*
         * 6. Membership pada alokasi pendapatan harus sama
         * dengan membership yang digunakan pada absensi.
         */

        DB::statement("
            ALTER TABLE membership_revenue_allocations
            ADD CONSTRAINT fk_revenue_allocation_attendance_membership
            FOREIGN KEY (attendance_id, membership_id)
            REFERENCES attendances (id, membership_id)
            ON DELETE RESTRICT
            ON UPDATE RESTRICT
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE membership_revenue_allocations
            DROP FOREIGN KEY fk_revenue_allocation_attendance_membership
        ");

        DB::statement("
            ALTER TABLE attendances
            DROP FOREIGN KEY fk_attendance_membership_owner
        ");

        DB::statement("
            ALTER TABLE memberships
            DROP FOREIGN KEY fk_memberships_detail_transaction_package
        ");

        DB::statement("
            ALTER TABLE memberships
            DROP FOREIGN KEY fk_memberships_transaction_member
        ");

        DB::statement("
            ALTER TABLE memberships
            DROP CONSTRAINT chk_memberships_transaction_detail_pair
        ");

        DB::statement("
            ALTER TABLE attendances
            DROP INDEX uq_attendances_id_membership
        ");

        DB::statement("
            ALTER TABLE memberships
            DROP INDEX uq_memberships_id_member
        ");

        DB::statement("
            ALTER TABLE transaction_details
            DROP INDEX uq_transaction_details_relation
        ");

        DB::statement("
            ALTER TABLE transactions
            DROP INDEX uq_transactions_id_member
        ");
    }
};
