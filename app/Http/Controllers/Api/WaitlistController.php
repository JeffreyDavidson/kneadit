<?php

namespace App\Http\Controllers\Api;

use App\Actions\Customers\JoinWaitlist;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreApiWaitlistRequest;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;

class WaitlistController extends Controller
{
    public function __invoke(StoreApiWaitlistRequest $request, JoinWaitlist $joinWaitlist): JsonResponse
    {
        $entry = $joinWaitlist($request->validated());

        return ApiResponse::created([
            'id' => $entry->id,
        ], 'Added to waitlist successfully.');
    }
}
