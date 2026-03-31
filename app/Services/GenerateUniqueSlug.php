<?php

namespace App\Services;

use Illuminate\Support\Str;

class GenerateUniqueSlug
{
    /**
     * @param class-string<\Illuminate\Database\Eloquent\Model> $modelClass
     */
    public function __invoke(string $modelClass, string $title): string
    {
        $slug = Str::slug($title);
        $original = $slug;
        $i = 2;

        while ($modelClass::query()->where('slug', $slug)->exists()) {
            $slug = "{$original}-{$i}";
            $i++;
        }

        return $slug;
    }
}
