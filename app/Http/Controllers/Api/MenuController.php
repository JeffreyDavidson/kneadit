<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Inventory\Category;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MenuController extends Controller
{
    public function __invoke(): AnonymousResourceCollection
    {
        $categories = Category::query()->active()
            ->orderBy('sort_order')
            ->with([
                'products' => function (HasMany $q): void {
                    $q->where('is_active', true);
                },
            ])
            ->get();

        return CategoryResource::collection($categories);
    }
}
