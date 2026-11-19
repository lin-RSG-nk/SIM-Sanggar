
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Validasi ketika absensi baru dibuat.
        DB::unprepared(<<<'SQL'
CREATE TRIGGER trg_attendance_payclass_before_insert
BEFORE INSERT ON attendances
FOR EACH ROW
BEGIN
    DECLARE v_valid INT DEFAULT 0;

    IF NEW.transaction_detail_id IS NOT NULL THEN

        SELECT COUNT(*)
        INTO v_valid
        FROM transaction_details td
        INNER JOIN transactions t
            ON t.id = td.transaction_id
        INNER JOIN class_schedules cs
            ON cs.id = NEW.class_schedule_id
        INNER JOIN class_rates cr
            ON cr.id = td.class_rate_id
        WHERE td.id = NEW.transaction_detail_id
          AND td.item_type = 'pay_per_class'
          AND td.class_schedule_id = NEW.class_schedule_id
          AND t.member_id = NEW.member_id
          AND t.transaction_type = 'pay_per_class'
          AND t.status = 'paid'
          AND cr.class_id = cs.class_id
          AND DATE(t.transaction_date) >= cr.effective_from
          AND (
                cr.effective_until IS NULL
                OR DATE(t.transaction_date) <= cr.effective_until
          );

        IF v_valid <> 1 THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT =
                'Absensi ditolak: transaksi pay-per-class tidak valid, belum dibayar, atau tidak sesuai member dan jadwal.';
        END IF;

    END IF;
END
SQL);

        // Validasi ketika data absensi diubah.
        DB::unprepared(<<<'SQL'
CREATE TRIGGER trg_attendance_payclass_before_update
BEFORE UPDATE ON attendances
FOR EACH ROW
BEGIN
    DECLARE v_valid INT DEFAULT 0;

    IF NEW.transaction_detail_id IS NOT NULL THEN

        SELECT COUNT(*)
        INTO v_valid
        FROM transaction_details td
        INNER JOIN transactions t
            ON t.id = td.transaction_id
        INNER JOIN class_schedules cs
            ON cs.id = NEW.class_schedule_id
        INNER JOIN class_rates cr
            ON cr.id = td.class_rate_id
        WHERE td.id = NEW.transaction_detail_id
          AND td.item_type = 'pay_per_class'
          AND td.class_schedule_id = NEW.class_schedule_id
          AND t.member_id = NEW.member_id
          AND t.transaction_type = 'pay_per_class'
          AND t.status = 'paid'
          AND cr.class_id = cs.class_id
          AND DATE(t.transaction_date) >= cr.effective_from
          AND (
                cr.effective_until IS NULL
                OR DATE(t.transaction_date) <= cr.effective_until
          );

        IF v_valid <> 1 THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT =
                'Perubahan absensi ditolak: transaksi pay-per-class tidak valid atau tidak sesuai.';
        END IF;

    END IF;
END
SQL);
    }

    public function down(): void
    {
        DB::unprepared(
            'DROP TRIGGER IF EXISTS trg_attendance_payclass_before_update'
        );

        DB::unprepared(
            'DROP TRIGGER IF EXISTS trg_attendance_payclass_before_insert'
        );
    }
};
