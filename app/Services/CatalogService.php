<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Gallery;
use App\Models\GalleryItem;
use App\Models\Image;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class CatalogService
{
    public function saveProduct(
        array $data,
        ?Product $product = null
    ): Product {
        return DB::transaction(function () use ($data, $product) {
            // Extract specifications before filling the Product model.
            $hasSpecifications = array_key_exists('specifications', $data);
            $specifications = $data['specifications'] ?? [];

            // Extract colors before filling the Product model.
            $hasColors = array_key_exists('color_ids', $data);
            $colorIds = $data['color_ids'] ?? [];

            unset($data['specifications'], $data['color_ids']);

            $product = $product
                ? Product::whereKey($product->id)
                    ->lockForUpdate()
                    ->firstOrFail()
                : new Product;

            $product->fill($data);

            // Validate the merged price state.
            if (
                $product->sale_price !== null
                && $this->cents($product->sale_price)
                    > $this->cents($product->price)
            ) {
                throw ValidationException::withMessages([
                    'sale_price' => __('catalog.sale_price_invalid'),
                ]);
            }

            $product->save();

            // Replace product colors only when color_ids is provided.
            if ($hasColors) {
                $syncData = [];

                foreach (array_values($colorIds) as $index => $colorId) {
                    $syncData[(int) $colorId] = [
                        'sort_order' => $index + 1,
                    ];
                }

                $product->colors()->sync($syncData);
            }

            // Replace specifications only when explicitly provided.
            if ($hasSpecifications) {
                $product->specifications()->delete();

                foreach ($specifications as $index => $specification) {
                    $product->specifications()->create([
                        'specification_id' =>
                            $specification['specification_id'],

                        'value_ar' =>
                            $specification['value_ar'] ?? null,

                        'value_en' =>
                            $specification['value_en'] ?? null,

                        'sort_order' =>
                            $specification['sort_order'] ?? ($index + 1),
                    ]);
                }
            }

            return $product->refresh()->load([
                'category',
                'images',
                'specifications.specification',
                'colors',
            ]);
        });
    }

    private function cents(string $amount): int
    {
        [$whole, $fraction] = array_pad(
            explode('.', $amount, 2),
            2,
            ''
        );

        return ((int) $whole * 100)
            + (int) str_pad($fraction, 2, '0');
    }

    public function deleteCategory(Category $category): void
    {
        DB::transaction(function () use ($category) {
            $category = Category::whereKey($category->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($category->products()->exists()) {
                throw ValidationException::withMessages([
                    'category' => __('catalog.category_has_products'),
                ]);
            }

            $category->delete();
        });
    }

    public function uploadImages(Product $product, array $files): array
    {
        $paths = [];

        try {
            return DB::transaction(function () use (
                $product,
                $files,
                &$paths
            ) {
                $product = Product::whereKey($product->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $sort = (int) $product->images()->max('sort_order');
                $images = [];

                foreach ($files as $file) {
                    $path = $file->store(
                        'products/' . $product->id,
                        'public'
                    );

                    if ($path === false) {
                        throw new RuntimeException(
                            'Product image storage failed.'
                        );
                    }

                    $paths[] = $path;

                    $images[] = $product->images()->create([
                        'image_path' => $path,
                        'sort_order' => ++$sort,
                    ]);
                }

                return $images;
            });
        } catch (Throwable $exception) {
            // Clean up files created by this request if the transaction fails.
            try {
                if (
                    $paths !== []
                    && ! Storage::disk('public')->delete($paths)
                ) {
                    Log::warning(
                        'Catalog upload rollback cleanup failed.',
                        ['paths' => $paths]
                    );
                }
            } catch (Throwable $cleanupException) {
                report($cleanupException);

                Log::warning(
                    'Catalog upload rollback cleanup requires retry.',
                    ['paths' => $paths]
                );
            }

            throw $exception;
        }
    }

    public function deleteImage(Product $product, Image $image): void
    {
        $path = DB::transaction(function () use ($product, $image) {
            $product = Product::whereKey($product->id)
                ->lockForUpdate()
                ->firstOrFail();

            $image = $product->images()
                ->whereKey($image->id)
                ->lockForUpdate()
                ->firstOrFail();

            $path = $image->image_path;

            $image->delete();

            return $path;
        });

        $this->deleteOwnedFile($product->id, $path);
    }

    public function deleteProduct(Product $product): void
    {
        $paths = DB::transaction(function () use ($product) {
            $product = Product::whereKey($product->id)
                ->lockForUpdate()
                ->firstOrFail();

            $paths = $product->images()
                ->pluck('image_path')
                ->all();

            $product->delete();

            return $paths;
        });

        foreach ($paths as $path) {
            $this->deleteOwnedFile($product->id, $path);
        }
    }

    private function deleteOwnedFile(
        int $productId,
        string $path
    ): void {
        // Only remove files matching the product's generated upload path.
        if (! preg_match(
            '#^products/'
                . preg_quote((string) $productId, '#')
                . '/[a-zA-Z0-9]+\.(jpg|jpeg|png|webp)$#D',
            $path
        )) {
            return;
        }

        try {
            if (
                Image::where('image_path', $path)->exists()
                || GalleryItem::where('image', $path)->exists()
            ) {
                return;
            }

            if (! Storage::disk('public')->delete($path)) {
                Log::warning(
                    'Catalog image cleanup failed.',
                    ['path' => $path]
                );
            }
        } catch (Throwable $exception) {
            report($exception);

            Log::warning(
                'Catalog image cleanup requires retry.',
                ['path' => $path]
            );
        }
    }

    public function saveGallery(
        array $data,
        ?Gallery $gallery = null
    ): Gallery {
        return DB::transaction(function () use ($data, $gallery) {
            $gallery = $gallery
                ? Gallery::whereKey($gallery->id)
                    ->lockForUpdate()
                    ->firstOrFail()
                : new Gallery;

            $gallery->fill(
                array_intersect_key($data, ['name' => true])
            );

            $gallery->save();

            if (array_key_exists('item_ids', $data)) {
                // Replace gallery membership without deleting image records.
                $items = GalleryItem::whereIn(
                    'id',
                    $data['item_ids']
                )
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get();

                if (
                    $items->count() !== count($data['item_ids'])
                ) {
                    throw ValidationException::withMessages([
                        'item_ids' => __('catalog.items_missing'),
                    ]);
                }

                $gallery->items()
                    ->whereNotIn('id', $data['item_ids'])
                    ->update(['gallery_id' => null]);

                GalleryItem::whereIn(
                    'id',
                    $data['item_ids']
                )->update([
                    'gallery_id' => $gallery->id,
                ]);
            }

            return $gallery->refresh()->load('items');
        });
    }
}
