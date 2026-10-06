<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Support\PlatformMailPreviews;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Local-only browser previews of KneadIt's own emails. Nothing is sent.
 */
class MailPreviewsController extends Controller
{
    public function index(): View
    {
        return view('central.mail-previews.index', [
            'titles' => PlatformMailPreviews::titles(),
        ]);
    }

    public function show(Request $request, string $mail): Response
    {
        $preview = PlatformMailPreviews::make($mail);

        abort_unless($preview instanceof Renderable, 404);

        if ($request->query('format') !== 'text') {
            return response($preview->render());
        }

        return response(PlatformMailPreviews::text($preview))->header('Content-Type', 'text/plain; charset=UTF-8');
    }
}
