<?php

return [
    'groups' => [
        'content' => 'Content',
    ],
    'types' => [
        'content.item_created'   => ['group' => 'content', 'label' => 'Item created',   'channels' => ['in_app'], 'defaults' => ['in_app' => false]],
        'content.item_published' => ['group' => 'content', 'label' => 'Item published',  'channels' => ['in_app'], 'defaults' => ['in_app' => false]],
    ],
];
