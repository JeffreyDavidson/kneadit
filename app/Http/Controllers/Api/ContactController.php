<?php

namespace App\Http\Controllers\Api;

use App\Actions\SubmitContactMessage;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreApiContactRequest;
use Illuminate\Http\JsonResponse;

class ContactController extends Controller
{
    public function __invoke(StoreApiContactRequest $request, SubmitContactMessage $submitMessage): JsonResponse
    {
        $submitMessage($request->validated());

        return response()->json([
            'data' => null,
            'message' => 'Message sent successfully.',
        ], 201);
    }
}
