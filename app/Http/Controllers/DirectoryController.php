<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DirectoryController extends Controller
{
    public function index(Request $request)
    {
        $bakeries = \App\Models\Tenant::where('is_active', true)
            ->where('storefront_enabled', true)
            ->with('domains')
            ->get()
            ->map(fn($t) => [
                'name' => $t->store_name ?? $t->name,
                'url' => 'http://' . $t->domains->first()?->domain,
                'color' => $t->brand_color_primary ?? '#d4920c',
            ]);

        return view('directory', compact('bakeries'));
    }
}
