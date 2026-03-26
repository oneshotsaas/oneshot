<?php

namespace OneShot\Content\Config;

use CodeIgniter\Config\BaseConfig;

class Content extends BaseConfig
{
    public int    $maxDepth   = 10;
    public string $uploadPath    = 'public/uploads/content/';   // path from ROOTPATH (disk)
    public string $uploadUrlPath = 'uploads/content/';           // URL path relative to base_url
    public string $cacheKey   = 'content_tree_v1';
}
