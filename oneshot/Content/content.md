# Content Module

Universal content management for pages, posts, categories (nested), and tags.

## Tables
- `content_items` — pages and posts (`type`: page|post), soft-deleted
- `content_categories` — nested categories (`parent_id`, unique slug per parent), soft-deleted
- `content_tags` — flat tags (global unique slug), soft-deleted
- `content_item_categories` — pivot: item ↔ category
- `content_item_tags` — pivot: item ↔ tag

## Key files
- `Services/Resolver.php` — in-memory URL resolver, boots from 3 DB queries, cached indefinitely; call `Resolver::flushContentCache()` after any write
- `Helpers/content.php` — all content helpers (see below)
- `Config/Content.php` — `maxDepth` (default 10), `uploadPath`, `uploadUrlPath`, `cacheKey`

## Helpers (`Helpers/content.php`)
- `content_url(object $entity, string $type)` — canonical URL for item/category/tag (static-cached, 1 DB query per request)
- `content_slugify(string $input)` — slug from any string, transliterates Unicode
- `content_category_paths()` — static-cached map `[category_id => 'parent/child']`
- `content_item_first_cats()` — static-cached map `[item_id => first_category_id]`
- `category_tree(array $flat, ?int $parentId, int $depth)` — adjacency list → nested tree
- `category_flat(array $flat)` — tree → flat ordered array with `->depth`, used for dropdowns
- `toc_extract(string $json)` — Editor.js JSON → `[{text, level, anchor}]` with deduplication
- `editorjs_render(string $json)` — Editor.js JSON → HTML (headings get `id` anchors, raw HTML sanitized)
- `_content_anchor(string $text)` — internal: slug-based anchor with md5 fallback

## URL resolution
Catch-all route `(:any)` with `priority=1000` (always last). Path normalized then walked segment by segment:

**Priority per segment:** category → tag → item

**Supported patterns:**
- `/slug` — item, category, or tag
- `/cat/sub` — nested category
- `/cat/post` — item validated against category membership
- `/cat/tag/post` — tag context preserved, item validated against category
- `/tag/post` — item under tag (no category validation)

Item accessed via non-canonical path → `301` redirect to canonical.

## Category slugs
Unique per parent (`UNIQUE(parent_id, slug)`). Same slug can exist under different parents.

## Item slugs
Globally unique. Conflict with an existing category slug → item unreachable under that slug path.

## Canonical URL
`content_items.canonical_category_id` (nullable) sets canonical path. If null — falls back to first assigned category. If no category — root `/{slug}`.

## Cache
No TTL — explicit invalidation only. Cache key: `content_tree_v1` (change key when map structure changes). Call `Resolver::flushContentCache()` from every admin write (Items, Categories, Tags store/update/destroy).

## Editor.js
Self-hosted bundles in `public/assets/content/editorjs/`. Scripts loaded lazily only on item form via `extra_scripts` slot (`Views/admin/items/_editorjs_scripts.php`).

Upload endpoints:
- `POST admin/content/upload-image` → `{ success, file: { url } }`
- `POST admin/content/fetch-url` → `{ success, meta: { title, description, image } }`

Uploaded files stored in `public/uploads/content/` (configurable via `Config/Content.php`).

## Admin nav
Content section in left nav: **Posts** (`?type=post`), **Pages** (`?type=page`), **Categories**, **Tags**. Filter state (`?type=`, `?cat=`) preserved through create → edit → save cycle via `_back` hidden field.
