<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Services\Platform\ChangelogService;
use Illuminate\Contracts\View\View;

class ChangelogController extends Controller
{
    public function __invoke(ChangelogService $changelog): View
    {
        return view('central.blog.changelog', [
            'entries' => $changelog->entries(),
        ]);
    }
}
