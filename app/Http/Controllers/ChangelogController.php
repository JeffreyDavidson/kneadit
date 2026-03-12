<?php

namespace App\Http\Controllers;

class ChangelogController extends Controller
{
    public function index()
    {
        $entries = config('changelog', []);

        return view('changelog', compact('entries'));
    }
}
