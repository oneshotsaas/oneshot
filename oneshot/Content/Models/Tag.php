<?php

namespace OneShot\Content\Models;

use OneShot\Core\Models\Base;

class Tag extends Base
{
    protected $table         = 'content_tags';
    protected $allowedFields = [
        'title', 'slug',
        'image', 'meta_title', 'meta_description',
        'template', 'is_active',
    ];

    public function getActive(): array
    {
        return $this->where('is_active', 1)
                    ->orderBy('title', 'ASC')
                    ->findAll();
    }
}
