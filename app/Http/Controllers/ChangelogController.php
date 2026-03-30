<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class ChangelogController extends Controller
{
    public function __invoke(): View
    {
        $entries = config('changelog', []);

        return view('changelog', [
            'entries' => $entries,
        ]);
    }
}
