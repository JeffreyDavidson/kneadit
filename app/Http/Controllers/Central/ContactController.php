<?php

namespace App\Http\Controllers\Central;

use App\Actions\Platform\SendMarketingContactMessage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Central\ContactRequest;
use Illuminate\Http\JsonResponse;

class ContactController extends Controller
{
    public function __invoke(ContactRequest $request, SendMarketingContactMessage $send): JsonResponse
    {
        $validated = $request->validated();

        $send(
            name: $validated['name'],
            email: $validated['email'],
            body: $validated['message'],
        );

        return response()->json([
            'success' => true,
            'message' => 'Message received',
        ]);
    }
}
