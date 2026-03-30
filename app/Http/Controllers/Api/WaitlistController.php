<?php

namespace App\Http\Controllers\Api;

use App\Enums\WaitlistStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreApiWaitlistRequest;
use App\Http\Responses\ApiResponse;
use App\Models\WaitlistEntry;
use Illuminate\Http\JsonResponse;

class WaitlistController extends Controller
{
    public function __invoke(StoreApiWaitlistRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $entry = WaitlistEntry::query()->create([
            ...$validated,
            'status' => WaitlistStatus::Waiting,
        ]);

        return ApiResponse::created([
            'id' => $entry->id,
        ], 'Added to waitlist successfully.');
    }
}
