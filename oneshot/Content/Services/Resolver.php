<?php

namespace OneShot\Content\Services;

class ResolveResult
{
    public function __construct(
        public readonly string $kind,  // 'item' | 'category' | 'tag'
        public readonly array  $data,
    ) {}
}

class Resolver
{
    private array $categoriesByParent  = [];  // [parent_id|null => [[id,slug,parent_id,template],...]]
    private array $categoriesById      = [];  // [id => [id,slug,parent_id]]
    private array $categoryPathMap     = [];  // [id => ['blog','ai',...]]
    private array $tagsBySlug          = [];  // [slug => [id,slug,template]]
    private array $itemsBySlug         = [];  // [slug => [id,slug,template,canonical_category_id]]
    private array $itemCategoriesMap   = [];  // [item_id => [category_id => true]]
    private bool  $booted              = false;

    public function boot(): void
    {
        if ($this->booted) return;

        $key    = config('Content')->cacheKey;
        $cached = cache($key);

        if ($cached) {
            $this->categoriesByParent = $cached['categoriesByParent'];
            $this->categoriesById     = $cached['categoriesById'];
            $this->categoryPathMap    = $cached['categoryPathMap'];
            $this->tagsBySlug         = $cached['tagsBySlug'];
            $this->itemsBySlug        = $cached['itemsBySlug'];
            $this->itemCategoriesMap  = $cached['itemCategoriesMap'];
            $this->booted = true;
            return;
        }

        $db = \Config\Database::connect();

        // Query 1 — UNION of all sluggable entities
        $sql = "
            SELECT 'item'     AS kind, id, slug, NULL      AS parent_id, template, NULL AS canonical_category_id
              FROM content_items WHERE deleted_at IS NULL AND is_active = 1
            UNION ALL
            SELECT 'category' AS kind, id, slug, parent_id, template, NULL
              FROM content_categories WHERE deleted_at IS NULL AND is_active = 1
            UNION ALL
            SELECT 'tag'      AS kind, id, slug, NULL,      template, NULL
              FROM content_tags WHERE deleted_at IS NULL AND is_active = 1
        ";
        $rows = $db->query($sql)->getResultArray();

        // Query 2 — pivot preload
        $pivotRows = $db->table('content_item_categories')->get()->getResultArray();

        // Query 3 — full category data for path building
        $catRows = $db->table('content_categories')
            ->select('id, parent_id, slug')
            ->where('deleted_at IS NULL')
            ->get()->getResultArray();

        // Build categoriesById
        foreach ($catRows as $row) {
            $this->categoriesById[(int)$row['id']] = [
                'id'        => (int)$row['id'],
                'slug'      => $row['slug'],
                'parent_id' => $row['parent_id'] !== null ? (int)$row['parent_id'] : null,
            ];
        }

        // Build categoryPathMap
        foreach ($this->categoriesById as $id => $_) {
            $this->categoryPathMap[$id] = $this->buildPathForCategory($id);
        }

        // Build maps from UNION rows
        foreach ($rows as $row) {
            $id       = (int)$row['id'];
            $parentId = $row['parent_id'] !== null ? (int)$row['parent_id'] : null;
            switch ($row['kind']) {
                case 'category':
                    if ($row['is_active'] ?? true) {
                        $this->categoriesByParent[$parentId][] = [
                            'id'        => $id,
                            'slug'      => $row['slug'],
                            'parent_id' => $parentId,
                            'template'  => $row['template'],
                        ];
                    }
                    break;
                case 'tag':
                    $this->tagsBySlug[$row['slug']] = [
                        'id'       => $id,
                        'slug'     => $row['slug'],
                        'template' => $row['template'],
                    ];
                    break;
                case 'item':
                    $this->itemsBySlug[$row['slug']] = [
                        'id'                    => $id,
                        'slug'                  => $row['slug'],
                        'template'              => $row['template'],
                        'canonical_category_id' => $row['canonical_category_id'] !== null ? (int)$row['canonical_category_id'] : null,
                    ];
                    break;
            }
        }

        // Build itemCategoriesMap (flipped for O(1) lookup)
        foreach ($pivotRows as $row) {
            $this->itemCategoriesMap[(int)$row['content_item_id']][(int)$row['content_category_id']] = true;
        }

        // Fix: canonical_category_id not in UNION (NULL placeholder) — reload from items
        $itemRows = $db->table('content_items')
            ->select('id, slug, template, canonical_category_id')
            ->where('deleted_at IS NULL')
            ->where('is_active', 1)
            ->get()->getResultArray();
        foreach ($itemRows as $row) {
            if (isset($this->itemsBySlug[$row['slug']])) {
                $this->itemsBySlug[$row['slug']]['canonical_category_id'] = $row['canonical_category_id'] !== null ? (int)$row['canonical_category_id'] : null;
            }
        }

        cache()->save($key, [
            'categoriesByParent' => $this->categoriesByParent,
            'categoriesById'     => $this->categoriesById,
            'categoryPathMap'    => $this->categoryPathMap,
            'tagsBySlug'         => $this->tagsBySlug,
            'itemsBySlug'        => $this->itemsBySlug,
            'itemCategoriesMap'  => $this->itemCategoriesMap,
        ], 0);

        $this->booted = true;
    }

    public function resolve(string $path): ?ResolveResult
    {
        $this->boot();

        $path     = rawurldecode($path);
        $path     = preg_replace('#/+#', '/', $path);
        $path     = strtolower(trim($path, '/'));
        $segments = array_values(array_filter(explode('/', $path)));

        if (empty($segments)) return null;

        if (count($segments) > config('Content')->maxDepth) {
            l(['error' => 'max_depth_exceeded', 'path' => $path], 'content_resolver');
            return null;
        }

        $categoryContext = null;
        $tagContext      = null;
        $resolved        = null;

        foreach ($segments as $i => $slug) {
            $isLast   = ($i === count($segments) - 1);
            $conflict = [];

            // 1. Try category
            $cat = $this->findCategory($slug, $categoryContext);
            if ($cat) {
                if (!empty($tagContext)) $conflict[] = 'tag';
                $categoryContext = $cat['id'];
                $tagContext      = null;
                $resolved        = new ResolveResult('category', $cat);
                if (!empty($conflict)) {
                    l(['conflict' => true, 'slug' => $slug, 'winner' => 'category', 'losers' => $conflict, 'path' => $path], 'content_resolver');
                }
                continue;
            }

            // 2. Try tag
            $tag = $this->tagsBySlug[$slug] ?? null;
            if ($tag) {
                $tagContext = $tag;
                $resolved   = new ResolveResult('tag', $tag);
                continue;
            }

            // 3. Try item — must be last segment
            $item = $this->itemsBySlug[$slug] ?? null;
            if ($item) {
                if (!$isLast) return null;
                if ($categoryContext !== null && !$this->itemBelongsToCategory($item['id'], $categoryContext)) {
                    l(['resolver_mismatch' => true, 'slug' => $slug, 'path' => $path, 'category_id' => $categoryContext], 'content_resolver');
                    return null;
                }
                $resolved = new ResolveResult('item', $item);
                continue;
            }

            // No match
            l(['resolver_miss' => true, 'slug' => $slug, 'path' => $path], 'content_resolver');
            return null;
        }

        return $resolved;
    }

    public function buildCanonicalPath(array $item): string
    {
        $catId = (int)($item['canonical_category_id'] ?? 0);

        // Fall back to first assigned category when canonical is not explicitly set
        if (!$catId) {
            $cats  = $this->itemCategoriesMap[(int)$item['id']] ?? [];
            $catId = (int)(array_key_first($cats) ?? 0);
        }

        if ($catId && isset($this->categoryPathMap[$catId])) {
            $parts   = $this->categoryPathMap[$catId];
            $parts[] = $item['slug'];
            return '/' . implode('/', $parts);
        }

        if ($catId) {
            l(['canonical_fallback' => true, 'item_id' => $item['id'], 'missing_category_id' => $catId], 'content_resolver');
        }
        return '/' . $item['slug'];
    }

    public function loadFull(string $kind, int $id): ?object
    {
        $db = \Config\Database::connect();
        switch ($kind) {
            case 'item':
                $item = $db->table('content_items')->where('id', $id)->get()->getRowObject();
                if (!$item) return null;
                $item->categories = $db->table('content_categories cc')
                    ->join('content_item_categories cic', 'cic.content_category_id = cc.id')
                    ->where('cic.content_item_id', $id)
                    ->get()->getResultObject();
                $item->tags = $db->table('content_tags ct')
                    ->join('content_item_tags cit', 'cit.content_tag_id = ct.id')
                    ->where('cit.content_item_id', $id)
                    ->get()->getResultObject();
                return $item;
            case 'category':
                $cat = $db->table('content_categories')->where('id', $id)->get()->getRowObject();
                if (!$cat) return null;
                $cat->subcategories = $db->table('content_categories')
                    ->where('parent_id', $id)
                    ->where('deleted_at IS NULL')
                    ->where('is_active', 1)
                    ->orderBy('sort', 'ASC')
                    ->orderBy('title', 'ASC')
                    ->get()->getResultObject();

                // Collect category ids to fetch items from: self + direct subcategories
                $catIds = [$id];
                foreach ($cat->subcategories as $sub) {
                    $catIds[] = (int)$sub->id;
                }

                $cat->items = $db->table('content_items ci')
                    ->distinct()
                    ->select('ci.*')
                    ->join('content_item_categories cic', 'cic.content_item_id = ci.id')
                    ->whereIn('cic.content_category_id', $catIds)
                    ->where('ci.deleted_at IS NULL')->where('ci.is_active', 1)
                    ->orderBy('ci.created_at', 'DESC')
                    ->get()->getResultObject();
                return $cat;
            case 'tag':
                $tag = $db->table('content_tags')->where('id', $id)->get()->getRowObject();
                if (!$tag) return null;
                $tag->items = $db->table('content_items ci')
                    ->join('content_item_tags cit', 'cit.content_item_id = ci.id')
                    ->where('cit.content_tag_id', $id)
                    ->where('ci.deleted_at IS NULL')->where('ci.is_active', 1)
                    ->orderBy('ci.created_at', 'DESC')
                    ->get()->getResultObject();
                return $tag;
        }
        return null;
    }

    public static function flushContentCache(): void
    {
        cache()->delete(config('Content')->cacheKey);
    }

    private function findCategory(string $slug, ?int $parentId): ?array
    {
        $list = $this->categoriesByParent[$parentId] ?? [];
        foreach ($list as $cat) {
            if ($cat['slug'] === $slug) return $cat;
        }
        return null;
    }

    private function itemBelongsToCategory(int $itemId, int $categoryId): bool
    {
        return isset($this->itemCategoriesMap[$itemId][$categoryId]);
    }

    private function buildPathForCategory(int $id): array
    {
        $parts   = [];
        $current = $id;
        $visited = [];
        while ($current && isset($this->categoriesById[$current])) {
            if (isset($visited[$current])) {
                l(['cycle_detected' => true, 'category_id' => $id, 'at' => $current], 'content_resolver');
                break;
            }
            $visited[$current] = true;
            $cat     = $this->categoriesById[$current];
            array_unshift($parts, $cat['slug']);
            $current = $cat['parent_id'];
        }
        return $parts;
    }
}
