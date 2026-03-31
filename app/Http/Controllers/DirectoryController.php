<?php

namespace App\Http\Controllers;

use App\Queries\ActiveBakeriesQuery;
use Illuminate\Contracts\View\View;

class DirectoryController extends Controller
{
    public function __invoke(): View
    {
        return view('directory', [
            'bakeries' => ActiveBakeriesQuery::get(),
        ]);
    }
}
