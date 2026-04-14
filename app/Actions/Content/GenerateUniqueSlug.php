<?php

namespace App\Actions\Content;

use Illuminate\Support\Str;

class GenerateUniqueSlug
{
    /**
     * @param class-string<\Illuminate\Database\Eloquent\Model> $modelClass
     */
    public function __invoke(string $modelClass, string $title, ?int $excludeId = null): string
    {
        $slug = Str::slug($title);
        $original = $slug;
        $i = 2;

        while (
            $modelClass::query()
                ->where('slug', $slug)
                ->when($excludeId, fn ($query) => $query->where('id', '!=', $excludeId))
                ->exists()
        ) {
            $slug = "{$original}-{$i}";
            $i++;
        }

        return $slug;
    }
}
