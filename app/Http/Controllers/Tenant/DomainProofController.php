<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Services\Platform\CustomDomainProof;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DomainProofController extends Controller
{
    /**
     * Answers with the ownership proof for the requested host, so a custom domain served
     * through a proxy can show that it reaches this application. Never cached.
     */
    public function __invoke(Request $request, CustomDomainProof $proof): Response
    {
        return response($proof->for($request->getHost()), 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Cache-Control' => 'no-store, private',
        ]);
    }
}
