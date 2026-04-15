<?php

use App\Enums\Financial\GiftCardTransactionType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $type = GiftCardTransactionType::Redemption->value;

        DB::statement('
            UPDATE orders
            SET gift_card_amount = (
                SELECT ABS(SUM(amount))
                FROM gift_card_transactions
                WHERE gift_card_transactions.order_id = orders.id
                AND gift_card_transactions.type = ?
            )
            WHERE gift_card_amount = 0
            AND id IN (
                SELECT order_id
                FROM gift_card_transactions
                WHERE order_id IS NOT NULL
                AND type = ?
            )
        ', [$type, $type]);
    }
};
