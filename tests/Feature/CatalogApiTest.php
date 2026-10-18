<?php

namespace Tests\Feature;

use App\Http\Resources\CategoryResource;
use App\Http\Resources\ProductResource;
use App\Models\Category;
use App\Models\Content;
use App\Models\Gallery;
use App\Models\GalleryItem;
use App\Models\Image;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class CatalogApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    private function admin(): User
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->givePermissionTo(Permission::findOrCreate('manage-settings', 'sanctum'));
        Sanctum::actingAs($user);

        return $user;
    }

    private function category(array $attributes = []): Category
    {
        static $number = 0;

        return Category::create(array_merge([
            'name_ar' => 'إضاءة داخلية', 'name_en' => 'Indoor Lighting',
            'slug' => 'lighting-'.++$number,
        ], $attributes))->refresh();
    }

    private function product(?Category $category = null, array $attributes = []): Product
    {
        static $number = 0;

        return Product::create(array_merge([
            'category_id' => ($category ?? $this->category())->id,
            'name_ar' => 'مصباح معلق', 'name_en' => 'Pendant Light',
            'slug' => 'pendant-'.++$number, 'price' => '1250.00',
        ], $attributes))->refresh();
    }

    private function png(): UploadedFile
    {
        return UploadedFile::fake()->createWithContent('light.png', base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jRZkAAAAASUVORK5CYII='
        ));
    }

    public function test_category_crud_and_existing_gallery_items_are_reused(): void
    {
        $this->admin();
        $item = GalleryItem::create(['image' => 'gallery/original.png']);
        $gallery = $this->postJson('/api/admin/galleries', [
            'name' => 'Lighting gallery', 'item_ids' => [$item->id],
        ])->assertCreated()->assertJsonPath('success', true)->json('data.id');

        $category = $this->postJson('/api/categories', [
            'name_ar' => 'إضاءة', 'name_en' => 'Lighting', 'slug' => 'lighting', 'gallery_id' => $gallery,
        ])->assertCreated()->assertJsonPath('data.gallery.items.0.image', 'gallery/original.png')->json('data.id');
        $this->getJson('/api/categories/'.$category)->assertOk()->assertJsonPath('data.gallery.id', $gallery);
        $this->patchJson('/api/categories/'.$category, ['name_en' => 'Indoor Lighting'])
            ->assertOk()->assertJsonPath('data.name_ar', 'إضاءة');
        $this->deleteJson('/api/categories/'.$category)->assertOk()->assertJsonPath('data', null);
        $this->assertDatabaseHas('gallery_items', ['id' => $item->id, 'image' => 'gallery/original.png']);
        $this->assertDatabaseHas('galleries', ['id' => $gallery]);
    }

    public function test_product_crud_nested_resources_and_original_localized_fields(): void
    {
        $this->admin();
        $category = $this->category();
        $id = $this->postJson('/api/products', [
            'category_id' => $category->id, 'name_ar' => 'مصباح', 'name_en' => 'Light',
            'description_ar' => 'وصف عربي أصلي', 'description_en' => 'Original English',
            'slug' => 'new-light', 'sku' => 'LIGHT-001', 'price' => '1250.00', 'sale_price' => '1000.00',
        ])->assertCreated()->assertJsonPath('data.price', '1250.00')
            ->assertJsonPath('data.is_featured', false)->assertJsonPath('data.category.id', $category->id)
            ->assertJsonPath('data.images', [])->json('data.id');

        foreach (['en', 'ar'] as $locale) {
            app()->setLocale($locale);
            $this->getJson('/api/products/'.$id)->assertOk()
                ->assertJsonPath('data.name_ar', 'مصباح')->assertJsonPath('data.name_en', 'Light')
                ->assertJsonPath('data.description_ar', 'وصف عربي أصلي')
                ->assertJsonPath('data.description_en', 'Original English');
        }
        app()->setLocale('en');
        $this->patchJson('/api/products/'.$id, ['is_featured' => true])->assertOk()->assertJsonPath('data.is_featured', true);
        $this->deleteJson('/api/products/'.$id)->assertOk()->assertJsonPath('success', true);
        $this->assertDatabaseMissing('products', ['id' => $id]);
        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    public function test_sale_price_validation_applies_to_merged_partial_updates(): void
    {
        $this->admin();
        $product = $this->product(attributes: ['price' => '100.00', 'sale_price' => '80.00']);
        foreach ([['sale_price' => '101.00'], ['price' => '79.99'], ['sale_price' => '-1.00'], ['price' => '1.001']] as $payload) {
            $this->patchJson('/api/products/'.$product->id, $payload)
                ->assertUnprocessable()->assertJsonPath('success', false)->assertJsonStructure(['message', 'errors']);
        }
        $this->assertSame('100.00', $product->fresh()->price);
        $this->patchJson('/api/products/'.$product->id, ['sale_price' => null, 'price' => '0.01'])
            ->assertOk()->assertJsonPath('data.sale_price', null)->assertJsonPath('data.price', '0.01');
    }

    public function test_validation_uniqueness_and_untrusted_fields(): void
    {
        $this->admin();
        $category = $this->category(['slug' => 'unique-category']);
        $product = $this->product($category, ['sku' => 'UNIQUE-SKU']);
        $this->postJson('/api/categories', [])->assertUnprocessable()
            ->assertJsonPath('success', false)->assertJsonValidationErrors(['name_ar', 'name_en', 'slug']);
        $this->postJson('/api/categories', ['name_ar' => 'اسم', 'name_en' => 'Name', 'slug' => $category->slug])
            ->assertUnprocessable()->assertJsonValidationErrors('slug');
        $this->postJson('/api/categories', ['name_ar' => 'اسم', 'name_en' => 'Name', 'slug' => 'Bad Slug', 'gallery_id' => 999])
            ->assertUnprocessable()->assertJsonValidationErrors(['slug', 'gallery_id']);
        $this->postJson('/api/products', [
            'category_id' => 999, 'name_ar' => 'اسم', 'name_en' => 'Name',
            'slug' => $product->slug, 'sku' => 'UNIQUE-SKU', 'price' => -1, 'status' => 'unknown',
        ])->assertUnprocessable()->assertJsonValidationErrors(['category_id', 'slug', 'sku', 'price', 'status']);
        $this->patchJson('/api/products/'.$product->id, ['slug' => $product->slug, 'sku' => $product->sku, 'id' => 999])
            ->assertOk()->assertJsonPath('data.id', $product->id);
    }

    public function test_pagination_category_featured_search_and_sort_filters(): void
    {
        $category = $this->category();
        $first = $this->product($category, ['sku' => 'FIND-ME', 'price' => '20.00', 'is_featured' => true]);
        $second = $this->product($category, ['price' => '10.00']);
        $this->product();
        $this->getJson('/api/products?category_id='.$category->id.'&per_page=1&sort=price&direction=asc')
            ->assertOk()->assertJsonPath('success', true)->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $second->id)->assertJsonPath('meta.total', 2)
            ->assertJsonPath('meta.current_page', 1)->assertJsonPath('meta.per_page', 1)
            ->assertJsonPath('meta.last_page', 2)->assertJsonStructure(['links']);
        $this->getJson('/api/products?featured=1&search=FIND-ME')->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $first->id);
        $this->getJson('/api/categories/'.$category->id.'/products')->assertOk()->assertJsonCount(2, 'data');
        foreach (['per_page=101', 'page=0', 'sort=invalid', 'direction=invalid', 'featured=anything', 'category_id[]=1'] as $query) {
            $this->getJson('/api/products?'.$query)->assertUnprocessable()->assertJsonPath('success', false);
        }
        $this->getJson('/api/categories?sort=price')->assertUnprocessable()->assertJsonValidationErrors('sort');
    }

    public function test_public_visibility_and_protected_admin_reads(): void
    {
        $inactive = $this->category(['status' => 'inactive']);
        $hiddenCategoryProduct = $this->product($inactive);
        $hiddenProduct = $this->product(attributes: ['status' => 'inactive']);
        $this->getJson('/api/products')->assertOk()->assertJsonCount(0, 'data');
        $this->getJson('/api/categories/'.$inactive->id)->assertNotFound()->assertJsonPath('success', false);
        $this->getJson('/api/products/'.$hiddenCategoryProduct->id)->assertNotFound();
        $this->getJson('/api/products/'.$hiddenProduct->id.'/images')->assertNotFound();
        $this->getJson('/api/admin/products')->assertUnauthorized();
        $this->admin();
        $this->getJson('/api/admin/products')->assertOk()->assertJsonCount(2, 'data');
        $this->getJson('/api/admin/products/'.$hiddenProduct->id)->assertOk();
        $this->getJson('/api/admin/categories/'.$inactive->id)->assertOk();
    }

    public function test_missing_and_empty_responses_have_one_envelope(): void
    {
        $this->getJson('/api/products')->assertOk()->assertExactJson([
            'success' => true, 'message' => 'Products retrieved successfully.', 'data' => [],
            'links' => ['first' => url('/api/products?page=1'), 'last' => url('/api/products?page=1'), 'prev' => null, 'next' => null],
            'meta' => [
                'current_page' => 1, 'from' => null, 'last_page' => 1,
                'links' => [
                    ['url' => null, 'label' => '&laquo; Previous', 'page' => null, 'active' => false],
                    ['url' => url('/api/products?page=1'), 'label' => '1', 'page' => 1, 'active' => true],
                    ['url' => null, 'label' => 'Next &raquo;', 'page' => null, 'active' => false],
                ],
                'path' => url('/api/products'), 'per_page' => 15, 'to' => null, 'total' => 0,
            ],
        ]);
        $this->getJson('/api/products/999')->assertNotFound()->assertExactJson([
            'success' => false, 'message' => 'The requested resource was not found.', 'errors' => null,
        ]);
        $this->getJson('/api/categories/999')->assertNotFound()->assertJsonPath('success', false);
    }

    public function test_write_permissions_and_inactive_accounts_are_enforced(): void
    {
        $this->postJson('/api/products', [])->assertUnauthorized()->assertJsonPath('success', false);
        Sanctum::actingAs(User::factory()->create(['is_active' => true]));
        $this->postJson('/api/categories', [])->assertForbidden()->assertJsonPath('success', false);
        $user = $this->admin();
        $user->update(['is_active' => false]);
        $this->postJson('/api/categories', [])->assertForbidden()->assertJsonPath('success', false);
        $this->postJson('/api/admin/galleries', [])->assertForbidden();
    }

    public function test_category_delete_restricts_products_and_gallery_delete_preserves_assets(): void
    {
        $this->admin();
        $gallery = Gallery::create(['name' => 'Original gallery']);
        $item = GalleryItem::create(['image' => 'gallery/shared.png', 'gallery_id' => $gallery->id]);
        Storage::disk('public')->put($item->image, 'original');
        $category = $this->category(['gallery_id' => $gallery->id]);
        $product = $this->product($category);
        $this->deleteJson('/api/categories/'.$category->id)->assertUnprocessable()->assertJsonValidationErrors('category');
        $this->assertDatabaseHas('products', ['id' => $product->id]);
        $this->deleteJson('/api/admin/galleries/'.$gallery->id)->assertOk();
        $this->assertNull($item->fresh()->gallery_id);
        $this->assertNull($category->fresh()->gallery_id);
        Storage::disk('public')->assertExists($item->image);
    }

    public function test_gallery_reassignment_changes_membership_without_copying_items_or_content(): void
    {
        $this->admin();
        $item = GalleryItem::create(['image' => 'gallery/original.png']);
        $content = Content::create(['page' => 'home', 'section' => 'hero', 'key' => 'image', 'type' => 'image', 'gallery_id' => $item->id]);
        $gallery = Gallery::create(['name' => 'Category gallery']);
        $this->patchJson('/api/admin/galleries/'.$gallery->id, ['item_ids' => [$item->id]])->assertOk()->assertJsonCount(1, 'data.items');
        $this->patchJson('/api/admin/galleries/'.$gallery->id, ['item_ids' => []])->assertOk()->assertJsonCount(0, 'data.items');
        $this->assertSame($item->id, $content->fresh()->gallery_id);
        $this->assertDatabaseCount('gallery_items', 1);
        $this->assertNull($item->fresh()->gallery_id);
    }

    public function test_multi_image_upload_order_metadata_scoped_access_and_delete(): void
    {
        $this->admin();
        $product = $this->product();
        $otherProduct = $this->product();
        $response = $this->postJson('/api/products/'.$product->id.'/images', ['images' => [$this->png(), $this->png()]])
            ->assertCreated()->assertJsonCount(2, 'data')->assertJsonPath('success', true);
        $imageId = $response->json('data.0.id');
        $path = $response->json('data.0.image_path');
        Storage::disk('public')->assertExists($path);
        $this->assertStringStartsWith('products/'.$product->id.'/', $path);
        $this->patchJson('/api/products/'.$product->id.'/images/'.$imageId, ['alt_text' => 'مصباح', 'sort_order' => 10])
            ->assertOk()->assertJsonPath('data.alt_text', 'مصباح');
        $this->getJson('/api/products/'.$product->id)->assertOk()->assertJsonPath('data.images.1.id', $imageId);
        $this->getJson('/api/products/'.$product->id.'/images?per_page=1')->assertOk()->assertJsonPath('meta.total', 2);
        $this->patchJson('/api/products/'.$otherProduct->id.'/images/'.$imageId, ['alt_text' => 'Wrong owner'])
            ->assertNotFound()->assertJsonPath('success', false);
        $this->deleteJson('/api/products/'.$otherProduct->id.'/images/'.$imageId)->assertNotFound();
        $this->patchJson('/api/products/'.$product->id.'/images/'.$imageId, ['image_path' => '../secret', 'product_id' => $otherProduct->id])
            ->assertUnprocessable()->assertJsonValidationErrors(['image_path', 'product_id']);
        $this->deleteJson('/api/products/'.$product->id.'/images/'.$imageId)->assertOk();
        Storage::disk('public')->assertMissing($path);
    }

    public function test_upload_validation_and_database_failure_cleanup(): void
    {
        $this->admin();
        $product = $this->product();
        $this->postJson('/api/products/'.$product->id.'/images', ['images' => [UploadedFile::fake()->create('bad.txt', 1, 'text/plain')]])
            ->assertUnprocessable()->assertJsonValidationErrors('images.0');
        $this->postJson('/api/products/'.$product->id.'/images', ['images' => []])->assertUnprocessable();

        $createdCount = 0;
        Image::creating(function () use (&$createdCount) {
            if (++$createdCount === 2) {
                throw new \RuntimeException('Private database detail must not leak');
            }
        });
        try {
            $this->postJson('/api/products/'.$product->id.'/images', ['images' => [$this->png(), $this->png()]])
                ->assertStatus(500)->assertExactJson([
                    'success' => false, 'message' => 'An unexpected server error occurred.', 'errors' => null,
                ]);
            $this->assertSame([], Storage::disk('public')->allFiles('products/'.$product->id));
            $this->assertDatabaseCount('images', 0);
        } finally {
            Image::flushEventListeners();
        }
    }

    public function test_product_delete_only_removes_its_own_unshared_files(): void
    {
        $this->admin();
        $product = $this->product();
        $otherProduct = $this->product();
        $path = $this->postJson('/api/products/'.$product->id.'/images', ['images' => [$this->png()]])->assertCreated()->json('data.0.image_path');
        $otherPath = $this->postJson('/api/products/'.$otherProduct->id.'/images', ['images' => [$this->png()]])->assertCreated()->json('data.0.image_path');
        $galleryItem = GalleryItem::create(['image' => $otherPath]);
        $this->deleteJson('/api/products/'.$product->id)->assertOk();
        Storage::disk('public')->assertMissing($path);
        Storage::disk('public')->assertExists($otherPath);
        $this->deleteJson('/api/products/'.$otherProduct->id)->assertOk();
        Storage::disk('public')->assertExists($otherPath);
        $this->assertDatabaseHas('gallery_items', ['id' => $galleryItem->id]);
        $this->assertDatabaseCount('images', 0);
    }

    public function test_resources_do_not_lazy_load_or_expose_internal_fields(): void
    {
        $category = $this->category();
        $product = $this->product($category);
        $product->setAttribute('internal_secret', 'private');
        DB::enableQueryLog();
        DB::flushQueryLog();
        $productData = (new ProductResource($product))->resolve(request());
        $categoryData = (new CategoryResource($category))->resolve(request());
        $this->assertSame([], DB::getQueryLog());
        $this->assertArrayNotHasKey('category', $productData);
        $this->assertArrayNotHasKey('images', $productData);
        $this->assertArrayNotHasKey('internal_secret', $productData);
        $this->assertArrayNotHasKey('gallery', $categoryData);
        $this->assertArrayNotHasKey('products', $categoryData);
        DB::disableQueryLog();
    }

    public function test_listing_query_count_does_not_grow_per_product(): void
    {
        $category = $this->category();
        $this->product($category);
        DB::enableQueryLog();
        DB::flushQueryLog();
        $this->getJson('/api/products')->assertOk();
        $firstCount = count(DB::getQueryLog());
        DB::disableQueryLog();
        for ($index = 0; $index < 5; $index++) {
            $this->product($category);
        }
        DB::enableQueryLog();
        DB::flushQueryLog();
        $this->getJson('/api/products')->assertOk()->assertJsonCount(6, 'data');
        $this->assertSame($firstCount, count(DB::getQueryLog()));
        DB::disableQueryLog();
    }
}
