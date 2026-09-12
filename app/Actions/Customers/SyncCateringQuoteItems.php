<?php

namespace App\Actions\Customers;

use App\Models\Customers\CateringInquiry;
use App\Models\Customers\CateringInquiryItem;
use App\ValueObjects\Money;
use Illuminate\Support\Facades\DB;

class SyncCateringQuoteItems
{
    /**
     * @param list<array{id: int|null, name: string, quantity: int, unit_price: float, special_instructions: string|null}> $rows
     */
    public function __invoke(CateringInquiry $inquiry, array $rows): void
    {
        DB::transaction(function () use ($inquiry, $rows): void {
            CateringInquiry::query()
                ->whereKey($inquiry->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $existing = $inquiry->items()->get()->keyBy('id');
            $submittedIds = array_filter(
                array_column($rows, 'id'),
                fn (?int $id): bool => $id !== null,
            );

            foreach ($existing as $id => $item) {
                if (! in_array($id, $submittedIds, true)) {
                    $item->delete();
                }
            }

            foreach ($rows as $sortOrder => $row) {
                $attributes = [
                    'name' => $row['name'],
                    'quantity' => $row['quantity'],
                    'unit_price' => $row['unit_price'],
                    'special_instructions' => $row['special_instructions'],
                    'sort_order' => $sortOrder,
                ];

                $existingItem = $row['id'] !== null
                    ? $existing->get($row['id'])
                    : null;

                if ($existingItem instanceof CateringInquiryItem) {
                    $existingItem->update($attributes);

                    continue;
                }

                $inquiry->items()->create($attributes);
            }

            $sumCents = $inquiry->items()->get()
                ->sum(fn (CateringInquiryItem $item): int => $item->unit_price->cents() * $item->quantity);

            $inquiry->update(['quoted_amount' => Money::fromCents((int) $sumCents)]);
        });

        $inquiry->refresh();
    }
}
