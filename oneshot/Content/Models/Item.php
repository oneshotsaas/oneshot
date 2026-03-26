<?php

namespace OneShot\Content\Models;

use OneShot\Core\Models\Base;

class Item extends Base
{
    protected $table         = 'content_items';
    protected $allowedFields = [
        'type', 'title', 'slug', 'canonical_category_id',
        'image', 'meta_title', 'meta_description', 'content',
        'template', 'is_active',
    ];

    public function getWithRelations(int $id): ?object
    {
        $item = $this->getById($id);
        if (!$item) return null;

        $db = \Config\Database::connect();

        $item->category_ids = $db->table('content_item_categories')
            ->select('content_category_id')
            ->where('content_item_id', $id)
            ->get()->getResultArray();
        $item->category_ids = array_column($item->category_ids, 'content_category_id');

        $item->tag_ids = $db->table('content_item_tags')
            ->select('content_tag_id')
            ->where('content_item_id', $id)
            ->get()->getResultArray();
        $item->tag_ids = array_column($item->tag_ids, 'content_tag_id');

        return $item;
    }

    public function syncCategories(int $itemId, array $ids): void
    {
        $db = \Config\Database::connect();
        $db->table('content_item_categories')->where('content_item_id', $itemId)->delete();
        if (!empty($ids)) {
            $rows = array_map(fn($cid) => ['content_item_id' => $itemId, 'content_category_id' => (int)$cid], $ids);
            $db->table('content_item_categories')->insertBatch($rows);
        }
    }

    public function syncTags(int $itemId, array $ids): void
    {
        $db = \Config\Database::connect();
        $db->table('content_item_tags')->where('content_item_id', $itemId)->delete();
        if (!empty($ids)) {
            $rows = array_map(fn($tid) => ['content_item_id' => $itemId, 'content_tag_id' => (int)$tid], $ids);
            $db->table('content_item_tags')->insertBatch($rows);
        }
    }

    public function getByCategoryId(int $catId): array
    {
        return $this->db->table('content_items ci')
            ->select('ci.*')
            ->join('content_item_categories cic', 'cic.content_item_id = ci.id')
            ->where('cic.content_category_id', $catId)
            ->where('ci.is_active', 1)
            ->where('ci.deleted_at IS NULL')
            ->orderBy('ci.created_at', 'DESC')
            ->get()->getResultObject();
    }

    public function getByTagId(int $tagId): array
    {
        return $this->db->table('content_items ci')
            ->select('ci.*')
            ->join('content_item_tags cit', 'cit.content_item_id = ci.id')
            ->where('cit.content_tag_id', $tagId)
            ->where('ci.is_active', 1)
            ->where('ci.deleted_at IS NULL')
            ->orderBy('ci.created_at', 'DESC')
            ->get()->getResultObject();
    }

    public function insertWithUniqueSlug(array $data): int
    {
        $base = $data['slug'];
        for ($i = 2; $i <= 20; $i++) {
            try {
                $this->insert($data);
                return (int) $this->getInsertID();
            } catch (\CodeIgniter\Database\Exceptions\DatabaseException $e) {
                if (str_contains($e->getMessage(), 'Duplicate entry')) {
                    $data['slug'] = $base . '-' . $i;
                    l(['slug_retry' => true, 'base' => $base, 'attempt' => $i], 'content');
                    continue;
                }
                throw $e;
            }
        }
        throw new \RuntimeException("Failed to insert item with unique slug after 20 attempts: {$base}");
    }
}
