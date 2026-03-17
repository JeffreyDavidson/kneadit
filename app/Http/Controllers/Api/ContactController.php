<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $validated['subject'] = strip_tags($validated['subject']);
        $validated['message'] = strip_tags($validated['message']);

        ContactMessage::create($validated);

        return response()->json([
            'data' => null,
            'message' => 'Message sent successfully.',
        ], 201);
    }
}
