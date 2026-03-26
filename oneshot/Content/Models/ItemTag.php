<?php

namespace OneShot\Content\Models;

use CodeIgniter\Model;

class ItemTag extends Model
{
    protected $table         = 'content_item_tags';
    protected $allowedFields = ['content_item_id', 'content_tag_id'];
    protected $useTimestamps = false;
    protected $useSoftDeletes = false;
}
