
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Validasi saat membership baru diterbitkan.
        DB::unprepared(<<<'SQL'
CREATE TRIGGER trg_membership_purchase_before_insert
BEFORE INSERT ON memberships
FOR EACH ROW
BEGIN
    DECLARE v_valid INT DEFAULT 0;

    IF NEW.transaction_id IS NULL
       OR NEW.transaction_detail_id IS NULL THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT =
            'Membership harus berasal dari transaksi pembelian yang valid.';
    END IF;

    SELECT COUNT(*)
    INTO v_valid
    FROM transaction_details td
    INNER JOIN transactions t
        ON t.id = td.transaction_id
    WHERE td.id = NEW.transaction_detail_id
      AND td.transaction_id = NEW.transaction_id
      AND td.item_type = 'membership'
      AND td.membership_package_id = NEW.membership_package_id
      AND t.member_id = NEW.member_id
      AND t.transaction_type = 'membership'
      AND t.status = 'paid'
      AND t.paid_at IS NOT NULL;

    IF v_valid <> 1 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT =
            'Membership ditolak: transaksi belum lunas atau data paket dan member tidak sesuai.';
    END IF;
END
SQL);

        // Validasi jika referensi pembelian membership diubah.
        DB::unprepared(<<<'SQL'
CREATE TRIGGER trg_membership_purchase_before_update
BEFORE UPDATE ON memberships
FOR EACH ROW
BEGIN
    DECLARE v_valid INT DEFAULT 0;

    IF NOT (NEW.transaction_id <=> OLD.transaction_id)
       OR NOT (NEW.transaction_detail_id <=> OLD.transaction_detail_id)
       OR NOT (NEW.member_id <=> OLD.member_id)
       OR NOT (NEW.membership_package_id <=> OLD.membership_package_id) THEN

        IF NEW.transaction_id IS NULL
           OR NEW.transaction_detail_id IS NULL THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT =
                'Referensi pembelian membership tidak boleh dikosongkan.';
        END IF;

        SELECT COUNT(*)
        INTO v_valid
        FROM transaction_details td
        INNER JOIN transactions t
            ON t.id = td.transaction_id
        WHERE td.id = NEW.transaction_detail_id
          AND td.transaction_id = NEW.transaction_id
          AND td.item_type = 'membership'
          AND td.membership_package_id = NEW.membership_package_id
          AND t.member_id = NEW.member_id
          AND t.transaction_type = 'membership'
          AND t.status = 'paid'
          AND t.paid_at IS NOT NULL;

        IF v_valid <> 1 THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT =
                'Perubahan membership ditolak: referensi transaksi tidak valid.';
        END IF;

    END IF;
END
SQL);
    }

    public function down(): void
    {
        DB::unprepared(
            'DROP TRIGGER IF EXISTS trg_membership_purchase_before_update'
        );

        DB::unprepared(
            'DROP TRIGGER IF EXISTS trg_membership_purchase_before_insert'
        );
    }
};
