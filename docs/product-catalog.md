# Product catalog API

Implemented in the existing Laravel application: installed framework 12.67.0,
CLI PHP 8.2.12, MySQL driver connecting to MariaDB 10.4.32. No order, checkout,
inventory, specifications, production products, or administrative users were added.

## Setup and database safety

The five migrations are additive. At final inspection, `migrate:status` showed
all five already applied in batch 21, and read-only schema inspection confirmed
their tables/columns. No application-database migration command was issued by
this implementation; its migration execution was confined to test databases.
For another development/staging database, review and apply them as follows:

```powershell
php artisan migrate:status
php artisan migrate --path=database/migrations/2026_10_18_000001_create_galleries_table.php --path=database/migrations/2026_10_18_000002_add_gallery_id_to_gallery_items_table.php --path=database/migrations/2026_10_18_000003_create_categories_table.php --path=database/migrations/2026_10_18_000004_create_products_table.php --path=database/migrations/2026_10_18_000005_create_images_table.php
php artisan storage:link
php artisan serve
```

Run `storage:link` only if the public link is absent. The existing public disk and
storage URL configuration are reused. No application database reset, seed,
truncate, or data deletion was run.

Bootstrap repair: PHP files previously under `config/database` were recursively
loaded as configuration and caused class redeclarations. Migrations, the user
factory, and seeders were moved unchanged into their standard `database` folders.
Migration basenames remain unchanged, so already recorded migrations do not run
again. The existing `database/seeders/DatabaseSeeder.php` remains authoritative;
its older duplicate is preserved in `database/legacy/DatabaseSeeder.php.txt`.
This correction executes no migrations or seeders by itself.

## Schema and relationships

| Table | New fields and constraints |
| --- | --- |
| `galleries` | ID, required name, timestamps |
| `gallery_items` | Existing records/paths retained; new nullable indexed gallery FK, null on gallery deletion |
| `categories` | Arabic/English names/descriptions, unique slug, nullable gallery FK, active/inactive status, unsigned sort order, timestamps |
| `products` | Required category FK, Arabic/English names/descriptions, unique slug, nullable unique SKU, DECIMAL(12,2) price/sale price, status, featured, unsigned sort order, timestamps |
| `images` | Product FK, unique generated image path, nullable alt text, unsigned sort order, timestamps |

Foreign keys use compatible unsigned bigints on MySQL. Category/gallery deletion
sets nullable gallery assignments to null. Categories with products cannot be
deleted. Product deletion cascades only its image rows. Defaults: active status,
false featured, zero sort order. Compound indexes support status/category/featured
listing and image ordering. Non-null SKU values are unique; multiple null SKUs are
allowed. Uniqueness follows the database collation's case sensitivity.

Relationships: Category belongs to Gallery and has many Products; Product belongs
to Category and has many ordered Images; Image belongs to Product; Gallery has
many Items and Categories; GalleryItem belongs to Gallery and retains `contents()`.

**Existing `contents.gallery_id` still references GalleryItem.** New
`categories.gallery_id` and `gallery_items.gallery_id` reference Gallery. Their
distinct meanings preserve the existing content/image integration.

## Routes

All paths below start with `/api`. Detail routes use numeric IDs.

| Method | Path | Behavior |
| --- | --- | --- |
| GET | `/categories` | Paginated active categories with gallery/items |
| GET | `/categories/{category}` | Active category with gallery/items |
| GET | `/categories/{category}/products` | Paginated visible products via ProductCollection |
| POST | `/categories` | Create category |
| PUT/PATCH | `/categories/{category}` | Partial category update |
| DELETE | `/categories/{category}` | Delete empty category; 422 if products remain |
| GET | `/products` | Paginated visible products with category/images |
| GET | `/products/{product}` | Visible product with category/images |
| POST | `/products` | Create product |
| PUT/PATCH | `/products/{product}` | Partial product update |
| DELETE | `/products/{product}` | Delete product and owned image files |
| GET | `/products/{product}/images` | Paginated ordered images of a visible product |
| POST | `/products/{product}/images` | Multipart multi-image upload |
| PATCH | `/products/{product}/images/{image}` | Update alt text / ordering |
| DELETE | `/products/{product}/images/{image}` | Delete owned image |
| GET | `/admin/categories` and `/admin/categories/{category}` | Include inactive categories |
| GET | `/admin/products` and `/admin/products/{product}` | Include inactive products |
| GET/POST | `/admin/galleries` | Paginated gallery list / creation |
| GET/PUT/PATCH/DELETE | `/admin/galleries/{gallery}` | Gallery detail/update/delete |

Catalog reads are public except `/admin/*`. All writes and admin reads require
the existing `auth:sanctum`, `active`, `permission:manage-settings` middleware.
The configured manage-settings permission uses the sanctum guard. Obtain a bearer
token through existing `POST /api/login` using an already authorized active user.
No authentication system, permission grants, CORS changes, or new API version was
introduced. Unauthenticated requests return 401; insufficient access/inactive
accounts return 403. Image bindings and explicit ownership checks enforce product
boundaries.

Visible products must be active and belong to active categories. Public detail
and image URLs return 404 for hidden records even for authenticated admins; use
protected admin URLs to inspect inactive records.

## Validation and listing

Category create requires `name_ar`, `name_en`, `slug`. Product create additionally
requires existing `category_id` and `price`. Names are at most 255 characters,
descriptions nullable strings up to 10,000, and SKU nullable up to 100. Slugs use
lowercase ASCII letters/digits separated by single hyphens. Gallery IDs must exist.
Unknown attributes are excluded from mass assignment. Image paths and ownership
cannot be changed through metadata requests.

Prices accept 0–2 decimals from 0 through 9,999,999,999.99; sale price is nullable,
nonnegative, and at most the regular price. The service compares merged values
in minor units under a row lock, including when only one price is updated. Prices
serialize as fixed two-decimal strings. Status is active/inactive, featured uses
Laravel boolean validation, and sort order is 0–4,294,967,295.

List parameters:

- `page`: 1–1,000,000; `per_page`: 1–100, default 15.
- `search`: up to 255 characters in Arabic/English name; products also search SKU.
- `sort`: sort_order (default), name_en, created_at; product lists also allow price.
- `direction`: asc (default) / desc, with ID as a stable tie breaker.
- `status`: active/inactive, still subject to public visibility restrictions.
- Product lists: `category_id`, `featured=1` / `featured=0`.

Image/gallery lists use pagination parameters. Eager loading batches relationships;
resources use `whenLoaded()` without queries. Category reads do not load all
products; their dedicated products endpoint paginates. Sort columns are allowlisted.

## Galleries and image storage

Existing `POST /api/gallery` uploads are unchanged. Create/update a Gallery with
`name` and optional `item_ids` (up to 500 distinct existing IDs). Providing item_ids
explicitly **replaces membership**: excluded items become unassigned, provided
items move from previous galleries. Omitting item_ids preserves membership. No
image record/file is copied or deleted. Set the category's gallery_id to reference
this gallery. Gallery deletion preserves items/files and nulls assignments.

Product uploads use multipart `images[]`: 1–20 JPEG/PNG/WebP files, max 2 MiB each,
matching existing conventions. Random names are generated below
`products/{product_id}` on the public disk. DB rows store paths, never file bytes.
Client paths are not accepted. Failed multi-upload transactions remove newly
uploaded files, including files from earlier iterations.

Image metadata accepts nullable `alt_text` (255 chars) and sort_order. Images are
ordered by sort_order then ID. Product/image deletion commits database changes
before removing only generated files within that product's directory, and keeps
files referenced by another image or a gallery item. Legacy/out-of-directory paths
are retained. Filesystem cleanup failures are logged for retry rather than
reversing a committed deletion. Direct SQL cascading deletes rows but cannot clean
files; use API/service deletion for managed assets.

## Responses and localization

Only new catalog paths use CatalogResponse and CatalogResponses middleware.
Existing gallery, content, auth, services and other responses remain unchanged.
Laravel resources and native pagination prevent double data wrapping.

| Operation | Response |
| --- | --- |
| Create | 201, `{success:true,message,data:{...}}` |
| Read/update | 200, `{success:true,message,data:{...}}` |
| Paginated list | 200, `{success:true,message,data:[],links:{...},meta:{current_page,per_page,total,last_page,...}}` |
| Multi-upload | 201, `{success:true,message,data:[...]}` |
| Delete | 200, `{success:true,message,data:null}` |
| Validation | 422, `{success:false,message,errors:{field:[...]}}` |
| Missing record | 404, `{success:false,message,errors:null}` |
| Authentication/authorization | 401/403, `{success:false,message,errors:null}` |
| Unexpected error | 500, generic message, errors:null; underlying exception logged by Laravel |

Server responses omit traces, SQL and private error details even in debug mode.
Resources always preserve both original Arabic/English fields. Message translations
use `lang/en/catalog.php`, `lang/ar/catalog.php` and Laravel's current application
locale. No automatic translation or new global locale-selection rule is added.
See [api-resources.md](api-resources.md) for legacy representations.

## Postman

Import `docs/postman/product-catalog.postman_collection.json` (36 requests) and
`product-catalog.postman_environment.example.json`. Set base_url to the origin
(default `http://127.0.0.1:8000`, without `/api`). Set existing admin_email/password
and run Login, or supply token manually. Credentials are blank in shipped files.
Login captures token, create requests capture IDs, and run_id suffixes avoid
slug/SKU collisions. Clear the collection's run_id before a new independent run.

Run folders in order against development/test data. Select local files in both
upload requests before sending; Postman supplies the multipart boundary. Example
payloads match the Form Requests. Tests assert HTTP status, envelopes and pagination.
The final cleanup deletes the product/category/gallery from the sequence, leaving
gallery items intact. Do not run cleanup with IDs for records you intend to retain.
File-upload requests require local file selection and are not unattended examples.

## Verification and known limits

```powershell
php artisan test --filter='CatalogApiTest|CatalogMigrationTest|GalleryResourcesTest|PostmanCollectionTest'
php artisan test
php artisan route:list --path=api --except-vendor
```

PHPUnit uses SQLite memory, not configured application MySQL. Coverage includes
CRUD, gallery reuse, monetary validation, uniqueness, localization, filters and
pagination, visibility, permissions, ownership, safe deletion, upload rollback,
no lazy loading, query counts, migration/rollback preservation, and mapping every
Postman request to a real route with protected-write middleware.

All five catalog migrations also ran against a disposable MySQL-driver/MariaDB
database: **13 assertions passed** for DECIMAL values, FK policies, legacy gallery/
content preservation, and rollback. The test database was removed. The configured
application gallery count was unchanged during that check. Other
MySQL versions and live frontend integration remain unverified.

The full suite contains a pre-existing AuthPasswordResetTest failure: it expects
`Illuminate\Auth\Notifications\ResetPassword`, while User sends the project's
`App\Notifications\ResetPasswordNotification`. This was observed before catalog
implementation after bootstrap repair. Its behavior/test remain outside scope.
No static-analysis tool is configured in composer.json.

Final verification: 25 focused tests passed with 399 assertions; the full suite
ran 30 tests and 408 assertions, with 29 passing and the one existing password-reset
failure described above. PHP syntax checks passed for 46 changed PHP files.
Pint and `git diff --check` passed. Read-only smoke requests against the current
application database returned standardized HTTP 200 responses for `/api/categories`
and `/api/products`, and HTTP 404 for a missing product. No catalog records were
created by these smoke checks. All 36 Postman requests resolve to implemented
routes; file selection and end-to-end execution inside Postman remain manual.

## Changed implementation files

- Models: new Category, Product, Image, Gallery; existing GalleryItem updated.
- Migrations: five `database/migrations/2026_10_18_*.php` files.
- Form Requests: Store/UpdateCategoryRequest, Store/UpdateProductRequest,
  StoreProductImagesRequest, UpdateProductImageRequest, SaveGalleryRequest,
  CatalogIndexRequest, CategoryIndexRequest.
- Controllers: CategoryController, ProductController, ProductImageController,
  CatalogGalleryController; existing GalleryController resource integration.
- Services: `app/Services/CatalogQuery.php`, `CatalogService.php`.
- Resources: CategoryResource, ProductResource, ImageResource, GalleryResource,
  GalleryItemResource, ContentGalleryResource, ContentResource, CategoryCollection,
  ProductCollection, GalleryItemCollection.
- Responses/routing: `app/Support/CatalogResponse.php`,
  `app/Http/Middleware/CatalogResponses.php`, `bootstrap/app.php`, `routes/api.php`.
- Translations: `lang/{en,ar}/catalog.php`.
- Tests: CatalogApiTest, CatalogMigrationTest, GalleryResourcesTest,
  PostmanCollectionTest under `tests/Feature`.
- Documentation: this guide, api-resources.md, Postman collection/environment.
- Bootstrap repair: former config/database files relocated as described above.
