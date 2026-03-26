<?php

namespace OneShot\Content\Controllers\Admin;

use OneShot\Content\Models\{Item, Category, Tag};
use OneShot\Content\Services\Resolver;

class Items extends Content
{
    private Item $model;

    public function initController($request, $response, $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->model = new Item();
        $this->appendBC(__('content.items', 'Items'), route_to('admin.content.items'));
    }

    public function index(): string
    {
        helper('content');
        $type  = $this->request->getGet('type');
        $catId = (int)$this->request->getGet('cat');

        $query = $this->model->where('deleted_at IS NULL');
        if (in_array($type, ['post', 'page'], true)) {
            $query->where('type', $type);
        }
        if ($catId) {
            $ids = array_column(
                \Config\Database::connect()
                    ->table('content_item_categories')
                    ->where('content_category_id', $catId)
                    ->get()->getResultArray(),
                'content_item_id'
            );
            $items = $ids ? $query->whereIn('id', $ids)->orderBy($type === 'page' ? 'title' : 'created_at', $type === 'page' ? 'ASC' : 'DESC')->findAll() : [];
        } else {
            $items = $query->orderBy($type === 'page' ? 'title' : 'created_at', $type === 'page' ? 'ASC' : 'DESC')->findAll();
        }

        $this->share('page_actions_view', 'Content::admin/items/_actions');
        return $this->render('Content::admin/items/index', [
            'items'      => $items,
            'categories' => category_flat((new Category())->where('deleted_at IS NULL')->orderBy('sort')->orderBy('title')->findAll()),
            'filterType' => $type,
            'filterCat'  => $catId,
        ]);
    }

    public function create(): string
    {
        $type = $this->request->getGet('type');
        $back = $this->request->getGet('back') ?: $this->_backUrl();
        $this->appendBC(__('content.new', 'New'));
        $this->share('extra_scripts', render('Content::admin/items/_editorjs_scripts'));
        return $this->render('Content::admin/items/form', [
            'item'        => (object)['type' => in_array($type, ['post','page'], true) ? $type : 'post'],
            'categories'  => (new Category())->getActive(),
            'tags'        => (new Tag())->getActive(),
            'back'        => $back,
        ]);
    }

    public function store(): \CodeIgniter\HTTP\RedirectResponse
    {
        $back = $this->request->getPost('_back') ?: route_to('admin.content.items');
        $data = $this->request->getPost(['type','title','slug','canonical_category_id','image','meta_title','meta_description','content','template','is_active']);
        $data['slug']                  = content_slugify($data['slug'] ?: $data['title']);
        $data['canonical_category_id'] = $data['canonical_category_id'] ?: null;
        $data['is_active']             = (int)($data['is_active'] ?? 0);
        $data['content']               = $data['content'] ?: null;

        $categoryIds = array_filter((array)$this->request->getPost('category_ids'));
        $tagIds      = array_filter((array)$this->request->getPost('tag_ids'));

        if ($data['canonical_category_id'] !== null && !in_array($data['canonical_category_id'], $categoryIds)) {
            $data['canonical_category_id'] = null;
        }

        $id = $this->model->insertWithUniqueSlug($data);
        $this->model->syncCategories($id, $categoryIds);
        $this->model->syncTags($id, $tagIds);
        Resolver::flushContentCache();

        return $this->redirectWith($back, __('content.saved', 'Saved'));
    }

    public function edit(string $hash): string|\CodeIgniter\HTTP\RedirectResponse
    {
        $id   = signedId($hash);
        $item = $this->model->getWithRelations($id);
        if (!$item) {
            return $this->redirectWith(route_to('admin.content.items'), __('content.not_found', 'Not found'), 'error');
        }
        $back = $this->request->getGet('back') ?: $this->_backUrl();
        $this->appendBC($item->title);
        $this->share('extra_scripts', render('Content::admin/items/_editorjs_scripts'));
        return $this->render('Content::admin/items/form', [
            'item'       => $item,
            'categories' => (new Category())->getActive(),
            'tags'       => (new Tag())->getActive(),
            'back'       => $back,
        ]);
    }

    public function update(string $hash): \CodeIgniter\HTTP\RedirectResponse
    {
        $back = $this->request->getPost('_back') ?: route_to('admin.content.items');
        $id   = signedId($hash);
        $data = $this->request->getPost(['type','title','slug','canonical_category_id','image','meta_title','meta_description','content','template','is_active']);
        $data['slug']                  = content_slugify($data['slug'] ?: $data['title']);
        $data['canonical_category_id'] = $data['canonical_category_id'] ?: null;
        $data['is_active']             = (int)($data['is_active'] ?? 0);
        $data['content']               = $data['content'] ?: null;

        $categoryIds = array_filter((array)$this->request->getPost('category_ids'));
        $tagIds      = array_filter((array)$this->request->getPost('tag_ids'));

        if ($data['canonical_category_id'] !== null && !in_array($data['canonical_category_id'], $categoryIds)) {
            $data['canonical_category_id'] = null;
        }

        $this->model->save(array_merge($data, ['id' => $id]));
        $this->model->syncCategories($id, $categoryIds);
        $this->model->syncTags($id, $tagIds);
        Resolver::flushContentCache();

        return $this->redirectWith($back, __('content.saved', 'Saved'));
    }

    public function destroy(string $hash): \CodeIgniter\HTTP\RedirectResponse
    {
        $this->model->delete(signedId($hash));
        Resolver::flushContentCache();
        return $this->redirectWith(route_to('admin.content.items'), __('content.deleted', 'Deleted'));
    }

    private function _backUrl(): string
    {
        $q = array_filter([
            'type' => $this->request->getGet('type') ?? '',
            'cat'  => (int)$this->request->getGet('cat') ?: '',
        ]);
        $base = route_to('admin.content.items');
        return $q ? $base . '?' . http_build_query($q) : $base;
    }
}
