<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class CatalogQuery
{
    public function categories(array $filters, bool $admin = false): LengthAwarePaginator
    {
        $query = Category::query()->with('galleryItem');
        if (! $admin) {
            $query->visible();
        }
        $this->filter($query, $filters);

        return $this->paginate($query, $filters);
    }

    public function products(array $filters, bool $admin = false, ?Category $category = null): LengthAwarePaginator
    {
        $query = Product::query()->with(['category', 'images', 'specifications.specification', 'colors']);
        if (! $admin) {
            $query->visible();
        }
        if ($category) {
            $query->where('category_id', $category->id);
        }
        if (isset($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }
        if (array_key_exists('featured', $filters)) {
            $query->where('is_featured', $filters['featured']);
        }
        $this->filter($query, $filters, true);

        return $this->paginate($query, $filters);
    }

    private function filter(Builder $query, array $filters, bool $products = false): void
    {
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (isset($filters['search']) && $filters['search'] !== '') {
            $query->where(function (Builder $search) use ($filters, $products) {
                $search->where('name_ar', 'like', '%'.$filters['search'].'%')
                    ->orWhere('name_en', 'like', '%'.$filters['search'].'%');
                if ($products) {
                    $search->orWhere('sku', 'like', '%'.$filters['search'].'%');
                }
            });
        }
    }

    private function paginate(Builder $query, array $filters): LengthAwarePaginator
    {
        return $query->orderBy($filters['sort'] ?? 'sort_order', $filters['direction'] ?? 'asc')
            ->orderBy('id')
            ->paginate($filters['per_page'] ?? 15)
            ->appends($filters);
    }
}
