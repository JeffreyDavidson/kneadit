<?php

namespace App\Http\Controllers;

use App\Services\ChangelogService;
use Illuminate\Contracts\View\View;

class ChangelogController extends Controller
{
    public function __invoke(ChangelogService $changelog): View
    {
        return view('changelog', [
            'entries' => $changelog->entries(),
        ]);
    }
}
