<?php

if (!function_exists('renderNestedList')) {
    function renderNestedList(string $tag, array $items): string
    {
        $html = "<{$tag}>\n";
        foreach ($items as $item) {
            if (is_array($item)) {
                $text     = $item['content'] ?? '';
                $children = $item['items'] ?? [];
                $html    .= '<li>' . $text;
                if (!empty($children)) {
                    $html .= renderNestedList($tag, $children);
                }
                $html .= "</li>\n";
            } else {
                $html .= '<li>' . $item . "</li>\n";
            }
        }
        $html .= "</{$tag}>\n";
        return $html;
    }
}

if (!function_exists('content_category_paths')) {
    /**
     * Returns a map of [category_id => 'parent-slug/child-slug'] for all non-deleted categories.
     * Lazily loaded once per request via static cache.
     */
    function content_category_paths(): array
    {
        static $paths = null;
        if ($paths !== null) return $paths;

        $cats  = model('\OneShot\Content\Models\Category')->where('deleted_at IS NULL')->findAll();
        $index = [];
        foreach ($cats as $c) $index[(int)$c->id] = $c;

        $paths = [];
        foreach ($index as $id => $c) {
            $parts = []; $cur = $id; $seen = [];
            while ($cur && isset($index[$cur])) {
                if (isset($seen[$cur])) break;
                $seen[$cur] = true;
                array_unshift($parts, $index[$cur]->slug);
                $cur = $index[$cur]->parent_id ? (int)$index[$cur]->parent_id : null;
            }
            $paths[$id] = implode('/', $parts);
        }
        return $paths;
    }
}

if (!function_exists('content_item_first_cats')) {
    /**
     * Returns map [item_id => first_category_id] from pivot, loaded once per request.
     */
    function content_item_first_cats(): array
    {
        static $map = null;
        if ($map !== null) return $map;
        $map = [];
        $rows = \Config\Database::connect()->table('content_item_categories')->get()->getResultArray();
        foreach ($rows as $r) {
            $map[(int)$r['content_item_id']] ??= (int)$r['content_category_id'];
        }
        return $map;
    }
}

if (!function_exists('content_url')) {
    /**
     * Returns the canonical base_url() for any content entity.
     *
     * @param object $entity  Category, Tag, or Item object
     * @param string $type    'category' | 'tag' | 'item'
     */
    function content_url(object $entity, string $type): string
    {
        switch ($type) {
            case 'category':
                $paths = content_category_paths();
                return base_url($paths[(int)$entity->id] ?? $entity->slug);

            case 'tag':
                return base_url($entity->slug);

            case 'item':
                $catId = (int)($entity->canonical_category_id ?? 0);
                if (!$catId) {
                    $catId = content_item_first_cats()[(int)$entity->id] ?? 0;
                }
                if ($catId) {
                    $paths = content_category_paths();
                    if (isset($paths[$catId])) {
                        return base_url($paths[$catId] . '/' . $entity->slug);
                    }
                }
                return base_url($entity->slug);
        }
        return base_url($entity->slug);
    }
}

if (!function_exists('content_category_chain')) {
    /**
     * Returns ordered array of category objects from root to the given category id.
     * Each object has: id, parent_id, title, slug.
     * Lazily loads all categories once per request.
     */
    function content_category_chain(int $id): array
    {
        static $index  = null;
        static $chains = [];

        if (isset($chains[$id])) return $chains[$id];

        if ($index === null) {
            $rows  = model('\OneShot\Content\Models\Category')
                ->where('is_active', 1)
                ->where('deleted_at IS NULL')
                ->select('id, parent_id, title, slug')
                ->findAll();
            $index = [];
            foreach ($rows as $r) $index[(int)$r->id] = $r;
        }

        $chain   = [];
        $current = $id;
        $visited = [];
        while ($current && isset($index[$current])) {
            if (isset($visited[$current])) break;
            $visited[$current] = true;
            array_unshift($chain, $index[$current]);
            $current = (int)($index[$current]->parent_id ?? 0);
        }

        $chains[$id] = $chain;
        return $chain;
    }
}

if (!function_exists('content_slugify')) {
    function content_slugify(string $input): string
    {
        $s = mb_strtolower(trim($input));
        if (function_exists('transliterator_transliterate')) {
            $s = transliterator_transliterate('Any-Latin; Latin-ASCII', $s) ?? $s;
        } elseif (function_exists('iconv')) {
            $s = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s) ?: $s;
        }
        $s = preg_replace('/[\s_]+/', '-', $s);
        $s = preg_replace('/[^a-z0-9\-]/', '', $s);
        $s = preg_replace('/-{2,}/', '-', $s);
        return trim($s, '-');
    }
}

if (!function_exists('category_tree')) {
    /**
     * Build a nested tree from a flat array of category objects.
     * Each node gets ->children[] and ->depth.
     */
    function category_tree(array $flat, ?int $parentId = null, int $depth = 0): array
    {
        $tree = [];
        foreach ($flat as $cat) {
            $catParent = isset($cat->parent_id) ? (int)$cat->parent_id : null;
            $pid       = $catParent === 0 ? null : $catParent;
            if ($pid === $parentId) {
                $cat->depth    = $depth;
                $cat->children = category_tree($flat, (int)$cat->id, $depth + 1);
                $tree[]        = $cat;
            }
        }
        usort($tree, fn($a, $b) => ($a->sort ?? 0) <=> ($b->sort ?? 0));
        return $tree;
    }
}

if (!function_exists('category_flat')) {
    /**
     * Flatten a nested tree into a depth-aware array for <select> dropdowns.
     */
    function category_flat(array $flat): array
    {
        $tree   = category_tree($flat);
        $result = [];
        $dfs    = function (array $nodes) use (&$dfs, &$result) {
            foreach ($nodes as $node) {
                $result[] = $node;
                if (!empty($node->children)) {
                    $dfs($node->children);
                }
            }
        };
        $dfs($tree);
        return $result;
    }
}

if (!function_exists('_content_anchor')) {
    /**
     * Convert heading text to a safe ASCII anchor id.
     * Transliterates Cyrillic/Unicode before stripping non-ASCII characters.
     * Falls back to a short hash if the result is empty.
     */
    function _content_anchor(string $text): string
    {
        $s = content_slugify(strip_tags($text));
        return 'h-' . ($s !== '' ? $s : substr(md5($text), 0, 8));
    }
}

if (!function_exists('toc_extract')) {
    /**
     * Extract heading blocks from Editor.js JSON → [{text, level, anchor}].
     */
    function toc_extract(string $json): array
    {
        $data   = json_decode($json, true);
        $blocks = $data['blocks'] ?? [];
        $toc    = [];
        $seen   = [];
        foreach ($blocks as $block) {
            if (($block['type'] ?? '') === 'header') {
                $text   = strip_tags($block['data']['text'] ?? '');
                $level  = (int)($block['data']['level'] ?? 2);
                $base   = _content_anchor($text);
                $seen[$base] = ($seen[$base] ?? 0) + 1;
                $anchor = $seen[$base] > 1 ? $base . '-' . $seen[$base] : $base;
                $toc[]  = compact('text', 'level', 'anchor');
            }
        }
        return $toc;
    }
}

if (!function_exists('editorjs_render')) {
    /**
     * Render Editor.js JSON to HTML.
     * Raw HTML blocks are sanitized via CI4's clean(). All other text uses esc().
     */
    function editorjs_render(string $json): string
    {
        $data   = json_decode($json, true);
        $blocks = $data['blocks'] ?? [];
        $html      = '';
        $seenAnchors = [];

        foreach ($blocks as $block) {
            $type = $block['type'] ?? '';
            $d    = $block['data'] ?? [];

            switch ($type) {
                case 'header':
                    $level  = min(6, max(1, (int)($d['level'] ?? 2)));
                    $text   = $d['text'] ?? '';
                    $base   = _content_anchor($text);
                    $seenAnchors[$base] = ($seenAnchors[$base] ?? 0) + 1;
                    $anchor = $seenAnchors[$base] > 1 ? $base . '-' . $seenAnchors[$base] : $base;
                    $html  .= "<h{$level} id=\"" . esc($anchor) . "\">{$text}</h{$level}>\n";
                    break;

                case 'paragraph':
                    $html .= '<p>' . ($d['text'] ?? '') . "</p>\n";
                    break;

                case 'quote':
                    $caption = $d['caption'] ? '<cite>' . esc($d['caption']) . '</cite>' : '';
                    $html   .= '<blockquote><p>' . ($d['text'] ?? '') . "</p>{$caption}</blockquote>\n";
                    break;

                case 'list':
                    $tag   = ($d['style'] ?? 'unordered') === 'ordered' ? 'ol' : 'ul';
                    $html .= renderNestedList($tag, $d['items'] ?? []);
                    break;

                case 'image':
                    $url     = esc($d['file']['url'] ?? '');
                    $caption = esc($d['caption'] ?? '');
                    $classes = implode(' ', array_keys(array_filter([
                        'image--stretched'     => $d['stretched'] ?? false,
                        'image--with-border'   => $d['withBorder'] ?? false,
                        'image--with-background' => $d['withBackground'] ?? false,
                    ])));
                    $html .= "<figure class=\"{$classes}\"><img src=\"{$url}\" alt=\"{$caption}\">";
                    if ($caption) $html .= "<figcaption>{$caption}</figcaption>";
                    $html .= "</figure>\n";
                    break;

                case 'code':
                    $html .= '<pre><code>' . esc($d['code'] ?? '') . "</code></pre>\n";
                    break;

                case 'delimiter':
                    $html .= "<hr>\n";
                    break;

                case 'warning':
                    $html .= '<div class="warning"><strong>' . esc($d['title'] ?? '') . '</strong><p>' . esc($d['message'] ?? '') . "</p></div>\n";
                    break;

                case 'table':
                    $html .= "<table>\n";
                    $rows      = $d['content'] ?? [];
                    $withHead  = $d['withHeadings'] ?? false;
                    foreach ($rows as $ri => $row) {
                        $tag  = ($withHead && $ri === 0) ? 'th' : 'td';
                        $html .= '<tr>';
                        foreach ($row as $cell) {
                            $html .= "<{$tag}>{$cell}</{$tag}>";
                        }
                        $html .= "</tr>\n";
                    }
                    $html .= "</table>\n";
                    break;

                case 'embed':
                    $src  = esc($d['embed'] ?? '');
                    $w    = (int)($d['width'] ?? 580);
                    $h    = (int)($d['height'] ?? 320);
                    $html .= "<div class=\"embed\"><iframe src=\"{$src}\" width=\"{$w}\" height=\"{$h}\" allowfullscreen></iframe></div>\n";
                    break;

                case 'linkTool':
                    $link  = $d['link'] ?? '';
                    $meta  = $d['meta'] ?? [];
                    $title = esc($meta['title'] ?? $link);
                    $desc  = esc($meta['description'] ?? '');
                    $img   = esc($meta['image']['url'] ?? '');
                    $href  = esc($link);
                    $html .= "<a href=\"{$href}\" class=\"link-card\" target=\"_blank\" rel=\"noopener\">";
                    if ($img) $html .= "<img src=\"{$img}\" alt=\"\">";
                    $html .= "<span class=\"link-card__title\">{$title}</span>";
                    if ($desc) $html .= "<span class=\"link-card__desc\">{$desc}</span>";
                    $html .= "</a>\n";
                    break;

                case 'attaches':
                    $url  = esc($d['file']['url'] ?? '');
                    $name = esc($d['file']['name'] ?? ($d['title'] ?? 'Download'));
                    $size = isset($d['file']['size']) ? '<span class="attachment__size">' . esc(number_format($d['file']['size'] / 1048576, 1)) . ' MiB</span>' : '';
                    $html .= "<a href=\"{$url}\" class=\"attachment\" download>{$name}{$size}</a>\n";
                    break;

                case 'raw':
                    // Sanitize raw HTML to prevent XSS
                    $html .= clean($d['html'] ?? '') . "\n";
                    break;

                default:
                    // Unknown block types are skipped
                    break;
            }
        }

        return $html;
    }
}
