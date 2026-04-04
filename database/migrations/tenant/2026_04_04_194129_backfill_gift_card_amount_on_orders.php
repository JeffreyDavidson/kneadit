<?php

use App\Enums\Financial\GiftCardTransactionType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::table('orders')
            ->where('gift_card_amount', 0)
            ->whereIn(
                'id',
                fn ($query) => $query->select('order_id')
                    ->from('gift_card_transactions')
                    ->whereNotNull('order_id')
                    ->where('type', GiftCardTransactionType::Redemption->value),
            )
            ->update([
                'gift_card_amount' => DB::raw('(
                    SELECT ABS(SUM(amount))
                    FROM gift_card_transactions
                    WHERE gift_card_transactions.order_id = orders.id
                    AND gift_card_transactions.type = \'' . GiftCardTransactionType::Redemption->value . '\'
                )'),
            ]);
    }
};
