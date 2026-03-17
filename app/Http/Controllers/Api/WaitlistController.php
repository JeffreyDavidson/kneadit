<?php

namespace App\Http\Controllers\Api;

use App\Enums\WaitlistStatus;
use App\Http\Controllers\Controller;
use App\Models\WaitlistEntry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WaitlistController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['required', 'email', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:50'],
            'delivery_date' => ['required', 'date'],
            'product_id' => ['nullable', 'exists:products,id'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        if (isset($validated['notes'])) {
            $validated['notes'] = strip_tags($validated['notes']);
        }

        $entry = WaitlistEntry::create([
            ...$validated,
            'status' => WaitlistStatus::Waiting,
        ]);

        return response()->json([
            'data' => ['id' => $entry->id],
            'message' => 'Added to waitlist successfully.',
        ], 201);
    }
}
