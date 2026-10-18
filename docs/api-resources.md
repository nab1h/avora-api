# API resources and compatibility

See [product-catalog.md](product-catalog.md) for catalog setup, routes,
validation, Postman instructions, schema decisions, and verification.

Historical gallery/content contracts are preserved:

| Endpoint | Response |
| --- | --- |
| `GET /api/admin/gallery` | Bare gallery-item array, including `[]` |
| `POST /api/gallery` | `{ "data": [galleryItem, ...] }`, HTTP 200 |
| `DELETE /api/gallery/{galleryItem}` | `{ "message": "Image deleted successfully." }` |
| `DELETE /api/gallery/bulk-delete` | `{ "message": "Images deleted successfully." }` |
| `GET /api/contents/{page}` | `{ "data": [content, ...] }`; unknown pages return an empty array |

`GalleryItemResource` exposes `id`, relative `image` path, `created_at`,
`updated_at`. The new assignment column does not leak into that contract.
`GalleryItemCollection` collects it using Laravel's ResourceCollection conventions.

`ContentGalleryResource` preserves `content.gallery`: `id` and a public `image`
URL. Image content also returns that URL as `value`. A loaded missing relationship
returns `gallery: null`; an unloaded relationship is omitted with `whenLoaded()`.
Existing public/admin content controllers eager-load `galleryItem`.

`GalleryResource` represents the new Gallery model: `id`, `name`, and conditionally
loaded `items` using GalleryItemResource. CategoryResource embeds it through
`gallery`, and conditionally serializes loaded `products` via ProductCollection.
Category endpoints use a dedicated paginated products route instead of unbounded
product loading.

ProductResource exposes catalog fields and conditionally loaded `category` and
`images`. ImageResource exposes `id`, `image_path`, public `url`, `alt_text`,
`sort_order`. Prices are decimal strings; Arabic and English values are unchanged.
Resource serialization does not query unloaded relationships.

Only new catalog routes use the standardized success/error envelope. Other APIs
retain their existing responses. Laravel resource wrapping and native pagination
are reused without `data.data`. No frontend consumer code is present beyond
starter assets, so no frontend contract migration was needed.
