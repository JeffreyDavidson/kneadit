<?php

namespace App\Http\Controllers\Api;

use App\Actions\Customers\JoinWaitlist;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreApiWaitlistRequest;
use App\Http\Resources\WaitlistEntryResource;
use Illuminate\Http\JsonResponse;

class WaitlistController extends Controller
{
    public function __invoke(StoreApiWaitlistRequest $request, JoinWaitlist $joinWaitlist): JsonResponse
    {
        $entry = $joinWaitlist($request->validated());

        return WaitlistEntryResource::make($entry)->response()->setStatusCode(201);
    }
}
