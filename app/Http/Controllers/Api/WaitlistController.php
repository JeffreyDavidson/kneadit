<?php

namespace App\Http\Controllers\Api;

use App\Enums\WaitlistStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreApiWaitlistRequest;
use App\Models\WaitlistEntry;
use Illuminate\Http\JsonResponse;

class WaitlistController extends Controller
{
    public function __invoke(StoreApiWaitlistRequest $request): JsonResponse
    {
        $validated = $request->validated();

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
