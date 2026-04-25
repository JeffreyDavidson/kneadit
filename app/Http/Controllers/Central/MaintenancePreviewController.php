<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Routing\Attributes\Controllers\Authorize;

class MaintenancePreviewController extends Controller
{
    #[Authorize('platform-admin')]
    public function __invoke(Request $request): View
    {
        return view('platform.maintenance', [
            'message' => $request->string('message')->toString() ?: null,
            'scheduled_end' => $request->string('end')->toString() ?: null,
        ]);
    }
}
