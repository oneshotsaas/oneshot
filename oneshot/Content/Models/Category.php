<?php

namespace OneShot\Content\Models;

use OneShot\Core\Models\Base;

class Category extends Base
{
    protected $table         = 'content_categories';
    protected $allowedFields = [
        'parent_id', 'title', 'slug',
        'image', 'meta_title', 'meta_description', 'content',
        'template', 'sort', 'is_active',
    ];

    public function getActive(): array
    {
        return $this->where('is_active', 1)
                    ->orderBy('sort', 'ASC')
                    ->orderBy('title', 'ASC')
                    ->findAll();
    }

    public function slugExistsUnderParent(string $slug, ?int $parentId, ?int $excludeId = null): bool
    {
        $builder = $this->builder()
            ->where('slug', $slug)
            ->where('deleted_at IS NULL');

        if ($parentId === null) {
            $builder->where('parent_id IS NULL');
        } else {
            $builder->where('parent_id', $parentId);
        }

        if ($excludeId !== null) {
            $builder->where('id !=', $excludeId);
        }

        return $builder->countAllResults() > 0;
    }
}
