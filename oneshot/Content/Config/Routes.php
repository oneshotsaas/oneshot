<?php

$p = config('Prefixes');

// Admin routes
$routes->group($p->admin . '/content', ['filter' => 'admin'], function ($r) {
    // Items
    $r->get('/',                              '\OneShot\Content\Controllers\Admin\Items::index',        ['as' => 'admin.content.items']);
    $r->get('create',                         '\OneShot\Content\Controllers\Admin\Items::create',       ['as' => 'admin.content.items.create']);
    $r->post('create',                        '\OneShot\Content\Controllers\Admin\Items::store');
    $r->get('(:segment)/edit',                '\OneShot\Content\Controllers\Admin\Items::edit/$1',      ['as' => 'admin.content.items.edit']);
    $r->post('(:segment)/edit',               '\OneShot\Content\Controllers\Admin\Items::update/$1');
    $r->post('(:segment)/delete',             '\OneShot\Content\Controllers\Admin\Items::destroy/$1',   ['as' => 'admin.content.items.delete']);
    $r->post('upload-image',                  '\OneShot\Content\Controllers\Admin\Upload::image',       ['as' => 'admin.content.upload.image']);
    $r->post('upload-file',                   '\OneShot\Content\Controllers\Admin\Upload::file',        ['as' => 'admin.content.upload.file']);
    $r->post('fetch-url',                     '\OneShot\Content\Controllers\Admin\Upload::fetchUrl',    ['as' => 'admin.content.fetch.url']);

    // Categories
    $r->get('categories',                     '\OneShot\Content\Controllers\Admin\Categories::index',   ['as' => 'admin.content.categories']);
    $r->get('categories/create',              '\OneShot\Content\Controllers\Admin\Categories::create',  ['as' => 'admin.content.categories.create']);
    $r->post('categories/create',             '\OneShot\Content\Controllers\Admin\Categories::store');
    $r->get('categories/(:segment)/edit',     '\OneShot\Content\Controllers\Admin\Categories::edit/$1', ['as' => 'admin.content.categories.edit']);
    $r->post('categories/(:segment)/edit',    '\OneShot\Content\Controllers\Admin\Categories::update/$1');
    $r->post('categories/(:segment)/delete',  '\OneShot\Content\Controllers\Admin\Categories::destroy/$1', ['as' => 'admin.content.categories.delete']);

    // Tags
    $r->get('tags',                           '\OneShot\Content\Controllers\Admin\Tags::index',         ['as' => 'admin.content.tags']);
    $r->get('tags/create',                    '\OneShot\Content\Controllers\Admin\Tags::create',        ['as' => 'admin.content.tags.create']);
    $r->post('tags/create',                   '\OneShot\Content\Controllers\Admin\Tags::store');
    $r->get('tags/(:segment)/edit',           '\OneShot\Content\Controllers\Admin\Tags::edit/$1',       ['as' => 'admin.content.tags.edit']);
    $r->post('tags/(:segment)/edit',          '\OneShot\Content\Controllers\Admin\Tags::update/$1');
    $r->post('tags/(:segment)/delete',        '\OneShot\Content\Controllers\Admin\Tags::destroy/$1',    ['as' => 'admin.content.tags.delete']);
});

// Front catch-all — must be last; setPrioritize(true) in app/Config/Routes.php ensures this
$routes->get('(:any)', '\OneShot\Content\Controllers\Front\Resolver::resolve', ['as' => 'content.resolve', 'priority' => 1000]);
