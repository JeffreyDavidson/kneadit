<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Queries\Platform\ActiveBakeriesQuery;
use Illuminate\Contracts\View\View;

class DirectoryController extends Controller
{
    public function __invoke(): View
    {
        return view('platform.directory', [
            'bakeries' => ActiveBakeriesQuery::get(),
        ]);
    }
}
