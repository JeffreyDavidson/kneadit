<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreApiContactRequest;
use App\Models\ContactMessage;
use Illuminate\Http\JsonResponse;

class ContactController extends Controller
{
    public function __invoke(StoreApiContactRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $validated['subject'] = strip_tags($validated['subject']);
        $validated['message'] = strip_tags($validated['message']);

        ContactMessage::query()->create($validated);

        return response()->json([
            'data' => null,
            'message' => 'Message sent successfully.',
        ], 201);
    }
}
