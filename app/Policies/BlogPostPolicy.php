<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\BlogPost;
use App\Models\User;

class BlogPostPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasMinRole(UserRole::Staff);
    }

    public function view(User $user, BlogPost $blogPost): bool
    {
        return $user->hasMinRole(UserRole::Staff);
    }

    public function create(User $user): bool
    {
        return $user->hasMinRole(UserRole::Staff);
    }

    public function update(User $user, BlogPost $blogPost): bool
    {
        return $user->hasMinRole(UserRole::Staff);
    }

    public function delete(User $user, BlogPost $blogPost): bool
    {
        return $user->hasMinRole(UserRole::Staff);
    }
}
