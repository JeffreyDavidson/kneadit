<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant\Admin;

use App\Http\Controllers\Controller;
use App\Models\Financial\Expense;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Serves an expense receipt to staff who may view expenses (the route checks the policy).
 *
 * New receipts live on the private, per-bakery `receipts` disk. Receipts uploaded before
 * that disk existed are still on the public disk, so they are served from there too.
 */
class ShowExpenseReceiptController extends Controller
{
    public function __invoke(Expense $expense): StreamedResponse
    {
        $path = $expense->receipt_image;

        abort_unless(is_string($path) && $path !== '', 404);

        foreach (['receipts', 'public'] as $disk) {
            if (Storage::disk($disk)->exists($path)) {
                return Storage::disk($disk)->response($path);
            }
        }

        abort(404);
    }
}
