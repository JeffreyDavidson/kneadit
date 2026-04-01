<?php

namespace App\Http\Controllers\Api;

use App\Actions\Customers\SubmitContactMessage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreApiContactRequest;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;

class ContactController extends Controller
{
    public function __invoke(StoreApiContactRequest $request, SubmitContactMessage $submitMessage): JsonResponse
    {
        $submitMessage($request->validated());

        return ApiResponse::created(message: 'Message sent successfully.');
    }
}
