<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Hapus tiga trigger membership yang lama.
        $this->dropMembershipTriggers();

        // Hapus aturan status lama.
        DB::unprepared(
            'ALTER TABLE attendances
             DROP CONSTRAINT chk_attendance_status'
        );

        // Status yang diizinkan hanya present dan excused.
        DB::unprepared(
            "ALTER TABLE attendances
             ADD CONSTRAINT chk_attendance_status
             CHECK (attendance_status IN ('present', 'excused'))"
        );

        // Buat ulang trigger dengan aturan status baru.
        $this->createMembershipTriggers(false);
    }

    public function down(): void
    {
        // Hapus trigger versi baru.
        $this->dropMembershipTriggers();

        // Hapus aturan status versi baru.
        DB::unprepared(
            'ALTER TABLE attendances
             DROP CONSTRAINT chk_attendance_status'
        );

        // Kembalikan aturan status sebelumnya.
        DB::unprepared(
            "ALTER TABLE attendances
             ADD CONSTRAINT chk_attendance_status
             CHECK (attendance_status IN ('present', 'late', 'excused'))"
        );

        // Kembalikan trigger dengan dukungan status late.
        $this->createMembershipTriggers(true);
    }

    private function dropMembershipTriggers(): void
    {
        $triggers = [
            'trg_attendance_membership_after_delete',
            'trg_attendance_membership_before_insert',
            'trg_attendance_membership_before_update',
        ];

        foreach ($triggers as $trigger) {
            DB::unprepared(
                "DROP TRIGGER IF EXISTS {$trigger}"
            );
        }
    }

    private function createMembershipTriggers(bool $includeLate): void
    {
        // Dipakai untuk menentukan status yang mengurangi kuota.
        $statuses = $includeLate
            ? "'present', 'late'"
            : "'present'";

        // Trigger 1: Mengembalikan kuota setelah absensi dihapus.
        DB::unprepared(<<<SQL
CREATE TRIGGER trg_attendance_membership_after_delete
AFTER DELETE ON attendances
FOR EACH ROW
BEGIN
    IF OLD.membership_id IS NOT NULL
       AND OLD.attendance_status IN ({$statuses}) THEN

        UPDATE memberships
        SET remaining_quota = LEAST(
            remaining_quota + 1,
            COALESCE(total_quota, remaining_quota + 1)
        )
        WHERE id = OLD.membership_id
          AND remaining_quota IS NOT NULL;

    END IF;
END
SQL);

        // Trigger 2: Validasi absensi dan pengurangan kuota.
        DB::unprepared(<<<SQL
CREATE TRIGGER trg_attendance_membership_before_insert
BEFORE INSERT ON attendances
FOR EACH ROW
BEGIN
    DECLARE v_status VARCHAR(20);
    DECLARE v_start_date DATE;
    DECLARE v_end_date DATE;
    DECLARE v_remaining SMALLINT UNSIGNED;

    IF NEW.membership_id IS NOT NULL THEN

        SELECT
            status,
            start_date,
            end_date,
            remaining_quota
        INTO
            v_status,
            v_start_date,
            v_end_date,
            v_remaining
        FROM memberships
        WHERE id = NEW.membership_id
          AND member_id = NEW.member_id;

        IF v_status IS NULL OR v_status <> 'active' THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT =
                'Absensi ditolak: membership tidak aktif atau tidak ditemukan.';
        END IF;

        IF NEW.attendance_date < v_start_date
           OR NEW.attendance_date > v_end_date THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT =
                'Absensi ditolak: membership belum mulai atau sudah kedaluwarsa.';
        END IF;

        IF NEW.attendance_status IN ({$statuses})
           AND v_remaining IS NOT NULL THEN

            UPDATE memberships
            SET remaining_quota = remaining_quota - 1
            WHERE id = NEW.membership_id
              AND member_id = NEW.member_id
              AND status = 'active'
              AND NEW.attendance_date BETWEEN start_date AND end_date
              AND remaining_quota > 0;

            IF ROW_COUNT() <> 1 THEN
                SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT =
                    'Absensi ditolak: kuota membership sudah habis.';
            END IF;

        END IF;

    END IF;
END
SQL);

        // Trigger 3: Mengatur perubahan status dan membership.
        DB::unprepared(<<<SQL
CREATE TRIGGER trg_attendance_membership_before_update
BEFORE UPDATE ON attendances
FOR EACH ROW
BEGIN
    DECLARE v_status VARCHAR(20);
    DECLARE v_start_date DATE;
    DECLARE v_end_date DATE;
    DECLARE v_remaining SMALLINT UNSIGNED;

    DECLARE v_old_consumes BOOLEAN DEFAULT FALSE;
    DECLARE v_new_consumes BOOLEAN DEFAULT FALSE;

    SET v_old_consumes =
        OLD.membership_id IS NOT NULL
        AND OLD.attendance_status IN ({$statuses});

    SET v_new_consumes =
        NEW.membership_id IS NOT NULL
        AND NEW.attendance_status IN ({$statuses});

    /* Kembalikan kuota jika penggunaan sebelumnya dibatalkan. */
    IF v_old_consumes
       AND (
           NEW.membership_id IS NULL
           OR NEW.membership_id <> OLD.membership_id
           OR NOT v_new_consumes
       ) THEN

        UPDATE memberships
        SET remaining_quota = LEAST(
            remaining_quota + 1,
            COALESCE(total_quota, remaining_quota + 1)
        )
        WHERE id = OLD.membership_id
          AND remaining_quota IS NOT NULL;

    END IF;

    IF NEW.membership_id IS NOT NULL THEN

        SELECT
            status,
            start_date,
            end_date,
            remaining_quota
        INTO
            v_status,
            v_start_date,
            v_end_date,
            v_remaining
        FROM memberships
        WHERE id = NEW.membership_id
          AND member_id = NEW.member_id;

        IF v_status IS NULL OR v_status <> 'active' THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT =
                'Perubahan ditolak: membership tidak aktif.';
        END IF;

        IF NEW.attendance_date < v_start_date
           OR NEW.attendance_date > v_end_date THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT =
                'Perubahan ditolak: tanggal di luar masa membership.';
        END IF;

        /* Kurangi kuota jika penggunaan baru belum diperhitungkan. */
        IF v_new_consumes
           AND (
               NOT v_old_consumes
               OR OLD.membership_id IS NULL
               OR OLD.membership_id <> NEW.membership_id
           )
           AND v_remaining IS NOT NULL THEN

            UPDATE memberships
            SET remaining_quota = remaining_quota - 1
            WHERE id = NEW.membership_id
              AND member_id = NEW.member_id
              AND status = 'active'
              AND NEW.attendance_date BETWEEN start_date AND end_date
              AND remaining_quota > 0;

            IF ROW_COUNT() <> 1 THEN
                SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT =
                    'Perubahan ditolak: kuota membership sudah habis.';
            END IF;

        END IF;

    END IF;
END
SQL);
    }
};
