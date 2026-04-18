<?php

namespace App\Http\Controllers\Api;

use App\Actions\Customers\SubmitContactMessage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreApiContactRequest;
use Illuminate\Http\Response;

class ContactController extends Controller
{
    public function __invoke(StoreApiContactRequest $request, SubmitContactMessage $submitMessage): Response
    {
        $submitMessage($request->validated());

        return response()->noContent();
    }
}
