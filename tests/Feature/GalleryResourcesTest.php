<?php

namespace Tests\Feature;

use App\Http\Middleware\EnsureUserIsActive;
use App\Http\Resources\ContentResource;
use App\Http\Resources\GalleryItemCollection;
use App\Models\Content;
use App\Models\GalleryItem;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Tests\TestCase;

class GalleryResourcesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    public function test_gallery_list_preserves_bare_array_and_explicit_fields(): void
    {
        $item = GalleryItem::create(['image' => 'gallery/example.png']);
        $this->withoutMiddleware([Authenticate::class, EnsureUserIsActive::class, PermissionMiddleware::class]);

        $this->getJson('/api/admin/gallery')->assertOk()->assertExactJson([
            [
                'id' => $item->id,
                'image' => 'gallery/example.png',
                'created_at' => $item->created_at->toJSON(),
                'updated_at' => $item->updated_at->toJSON(),
            ],
        ]);
    }

    public function test_empty_lists_keep_their_existing_shapes(): void
    {
        $this->withoutMiddleware([Authenticate::class, EnsureUserIsActive::class, PermissionMiddleware::class]);
        $this->getJson('/api/admin/gallery')->assertOk()->assertExactJson([]);
        $this->getJson('/api/contents/missing')->assertOk()->assertExactJson(['data' => []]);
    }

    public function test_upload_uses_one_data_wrapper_and_stores_the_image(): void
    {
        $this->withoutMiddleware([Authenticate::class, EnsureUserIsActive::class, PermissionMiddleware::class]);
        $response = $this->postJson('/api/gallery', [
            'images' => [UploadedFile::fake()->createWithContent('example.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jRZkAAAAASUVORK5CYII='))],
        ])->assertOk()->assertJsonCount(1, 'data')
            ->assertJsonStructure(['data' => [['id', 'image', 'created_at', 'updated_at']]]);

        Storage::disk('public')->assertExists($response->json('data.0.image'));
        $this->assertArrayNotHasKey('data', $response->json('data.0'));
    }

    public function test_content_gallery_preserves_url_and_null_relationship_contracts(): void
    {
        $item = GalleryItem::create(['image' => 'gallery/example.png']);
        Content::create([
            'page' => 'home', 'section' => 'hero', 'key' => 'image',
            'type' => 'image', 'gallery_id' => $item->id,
        ]);
        $text = 'Original English / العربية الأصلية';
        Content::create([
            'page' => 'home', 'section' => 'hero', 'key' => 'title',
            'type' => 'text', 'value' => $text,
        ]);

        foreach (['en', 'ar'] as $locale) {
            app()->setLocale($locale);
            $this->getJson('/api/contents/home')->assertOk()
                ->assertJsonPath('data.0.gallery', [
                    'id' => $item->id,
                    'image' => Storage::disk('public')->url($item->image),
                ])
                ->assertJsonPath('data.0.value', Storage::disk('public')->url($item->image))
                ->assertJsonPath('data.1.gallery', null)
                ->assertJsonPath('data.1.value', $text);
        }
    }

    public function test_serialization_does_not_query_unloaded_relationships_or_expose_extra_attributes(): void
    {
        $content = new Content(['type' => 'text', 'value' => 'Original']);
        $item = new GalleryItem(['image' => 'gallery/example.png']);
        $item->setAttribute('internal_secret', 'hidden');
        DB::enableQueryLog();
        DB::flushQueryLog();

        $data = (new ContentResource($content))->resolve(request());
        $items = (new GalleryItemCollection(collect([$item])))->resolve(request());

        $this->assertArrayNotHasKey('gallery', $data);
        $this->assertArrayNotHasKey('internal_secret', $items[0]);
        $this->assertSame([], DB::getQueryLog());
        DB::disableQueryLog();
    }

    public function test_collection_supports_native_laravel_pagination_without_double_nesting(): void
    {
        GalleryItem::create(['image' => 'gallery/example.png']);
        $response = (new GalleryItemCollection(GalleryItem::paginate(15)))
            ->toResponse(request())->getData(true);

        $this->assertCount(1, $response['data']);
        $this->assertSame(1, $response['meta']['current_page']);
        $this->assertSame(15, $response['meta']['per_page']);
        $this->assertSame(1, $response['meta']['total']);
        $this->assertSame(1, $response['meta']['last_page']);
        $this->assertArrayNotHasKey('data', $response['data'][0]);
    }

    public function test_validation_and_missing_resource_errors_preserve_status_and_shape(): void
    {
        $this->withoutMiddleware([Authenticate::class, EnsureUserIsActive::class, PermissionMiddleware::class]);
        $this->postJson('/api/gallery', [])->assertUnprocessable()->assertJsonValidationErrors('images');
        $this->deleteJson('/api/gallery/bulk-delete', ['ids' => ['invalid']])
            ->assertUnprocessable()->assertJsonValidationErrors('ids.0');
        $this->deleteJson('/api/gallery/999')->assertNotFound()
            ->assertExactJson(['message' => 'Resource not found.']);
    }

    public function test_gallery_administration_requires_authentication(): void
    {
        $this->getJson('/api/admin/gallery')->assertUnauthorized()
            ->assertExactJson(['message' => 'Unauthenticated.']);
    }
}
