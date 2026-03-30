<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DirectoryController extends Controller
{
    public function __invoke(Request $request): View
    {
        $bakeries = Tenant::query()->where('is_active', true)
            ->where('storefront_enabled', true)
            ->with('domains')
            ->get()
            ->map(fn (Tenant $t) => [
                'name' => $t->store_name ?? $t->name,
                'url' => 'http://' . $t->domains->first()?->domain,
                'color' => $t->brand_color_primary ?? '#d4920c',
            ]);

        return view('directory', [
            'bakeries' => $bakeries,
        ]);
    }
}
