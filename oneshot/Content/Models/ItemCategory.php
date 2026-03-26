<?php

namespace OneShot\Content\Models;

use CodeIgniter\Model;

class ItemCategory extends Model
{
    protected $table         = 'content_item_categories';
    protected $allowedFields = ['content_item_id', 'content_category_id'];
    protected $useTimestamps = false;
    protected $useSoftDeletes = false;
}
