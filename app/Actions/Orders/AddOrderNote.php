<?php

namespace App\Actions\Orders;

use App\Models\Order;

class AddOrderNote
{
    public function __invoke(Order $order, string $note): void
    {
        $currentNotes = $order->notes;
        $timestamp = now()->format('Y-m-d H:i:s');
        $newNote = "[{$timestamp}] {$note}";

        $updatedNotes = $currentNotes ? $currentNotes . "\n\n" . $newNote : $newNote;

        $order->update(['notes' => $updatedNotes]);
    }
}
