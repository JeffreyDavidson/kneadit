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
        $send(
            name: $request->string('name')->toString(),
            email: $request->string('email')->toString(),
            body: $request->string('message')->toString(),
        );

        return response()->json([
            'success' => true,
            'message' => 'Message received',
        ]);
    }
}
