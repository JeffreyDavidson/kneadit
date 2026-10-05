<?php

namespace App\Http\Controllers\Tenant\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Inventory\Category;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MenuController extends Controller
{
    public function __invoke(Request $request): AnonymousResourceCollection
    {
        $categories = Category::query()->active()
            ->orderBy('sort_order')
            ->with([
                'products' => fn (HasMany $q) => $q->where('is_active', true),
            ])
            ->get();

        // The menu always lists its products, so ask the JSON:API resource to include them.
        $request->merge(['include' => 'products']);

        return CategoryResource::collection($categories);
    }
}
