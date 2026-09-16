<?php

namespace App\Services\Tenants\Contracts;

interface LegacyRecipeImporter
{
    /**
     * @param  array<int, array<string, mixed>>  $recipes
     * @param  array<int, array<string, mixed>>  $ingredients
     * @param  array<int, array<string, mixed>>  $stages
     * @param  array<int, int>  $productIds
     */
    public function import(array $recipes, array $ingredients, array $stages, array $productIds): void;
}
