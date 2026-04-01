<?php

namespace App\Http\Requests\Api;

use App\Enums\Content\BlogPostCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexBlogPostsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $validCategories = collect(BlogPostCategory::cases())
            ->map(fn (BlogPostCategory $case) => $case->value)
            ->push('all')
            ->all();

        return [
            'category' => ['sometimes', 'string', Rule::in($validCategories)],
        ];
    }
}
