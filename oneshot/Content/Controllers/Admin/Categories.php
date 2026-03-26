<?php

namespace OneShot\Content\Controllers\Admin;

use OneShot\Content\Models\Category;
use OneShot\Content\Services\Resolver;

class Categories extends Content
{
    private Category $model;

    public function initController($request, $response, $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->model = new Category();
        $this->appendBC(__('content.categories', 'Categories'), route_to('admin.content.categories'));
    }

    public function index(): string
    {
        helper('content');
        $this->share('page_actions_view', 'Content::admin/categories/_actions');
        return $this->render('Content::admin/categories/index', [
            'categories' => category_flat($this->model->where('deleted_at IS NULL')->orderBy('sort')->orderBy('title')->findAll()),
        ]);
    }

    public function create(): string
    {
        $this->appendBC(__('content.new', 'New'));
        $this->share('extra_scripts', render('Content::admin/items/_editorjs_scripts'));
        return $this->render('Content::admin/categories/form', [
            'category'   => null,
            'categories' => $this->model->getActive(),
        ]);
    }

    public function store(): \CodeIgniter\HTTP\RedirectResponse
    {
        $data = $this->request->getPost(['parent_id','title','slug','image','meta_title','meta_description','content','template','sort','is_active']);
        $data['slug']      = content_slugify($data['slug'] ?: $data['title']);
        $data['parent_id'] = $data['parent_id'] ?: null;
        $data['sort']      = (int)($data['sort'] ?? 0);
        $data['is_active'] = (int)($data['is_active'] ?? 0);

        if ($this->model->slugExistsUnderParent($data['slug'], $data['parent_id'])) {
            return $this->redirectWith(route_to('admin.content.categories.create'), __('content.slug_taken', 'Slug already taken under this parent'), 'error');
        }

        // Check for conflict with tag slugs — non-blocking warning
        $tagConflict = \Config\Database::connect()->table('content_tags')
            ->where('slug', $data['slug'])->where('deleted_at IS NULL')->countAllResults() > 0;
        if ($tagConflict) {
            session()->setFlashdata('content_slug_warning', __('content.category_slug_tag_conflict', 'This slug also exists as a tag. The category will take priority in URL resolution.'));
        }

        $id = $this->model->add($data);
        Resolver::flushContentCache();
        return $this->redirectWith(route_to('admin.content.categories'), __('content.saved', 'Saved'));
    }

    public function edit(string $hash): string|\CodeIgniter\HTTP\RedirectResponse
    {
        $id       = signedId($hash);
        $category = $this->model->getById($id);
        if (!$category) {
            return $this->redirectWith(route_to('admin.content.categories'), __('content.not_found', 'Not found'), 'error');
        }
        $this->appendBC($category->title);
        $this->share('extra_scripts', render('Content::admin/items/_editorjs_scripts'));
        return $this->render('Content::admin/categories/form', [
            'category'   => $category,
            'categories' => $this->model->where('id !=', $id)->where('deleted_at IS NULL')->orderBy('sort')->findAll(),
        ]);
    }

    public function update(string $hash): \CodeIgniter\HTTP\RedirectResponse
    {
        $id   = signedId($hash);
        $data = $this->request->getPost(['parent_id','title','slug','image','meta_title','meta_description','content','template','sort','is_active']);
        $data['slug']      = content_slugify($data['slug'] ?: $data['title']);
        $data['parent_id'] = $data['parent_id'] ?: null;
        $data['sort']      = (int)($data['sort'] ?? 0);
        $data['is_active'] = (int)($data['is_active'] ?? 0);

        if ($data['parent_id'] !== null && $this->wouldCreateCycle($id, (int)$data['parent_id'])) {
            return $this->redirectWith(route_to('admin.content.categories.edit', signId($id)), __('content.cycle_error', 'This parent would create a circular reference'), 'error');
        }

        if ($this->model->slugExistsUnderParent($data['slug'], $data['parent_id'], $id)) {
            return $this->redirectWith(route_to('admin.content.categories.edit', signId($id)), __('content.slug_taken', 'Slug already taken under this parent'), 'error');
        }

        $tagConflict = \Config\Database::connect()->table('content_tags')
            ->where('slug', $data['slug'])->where('deleted_at IS NULL')->countAllResults() > 0;
        if ($tagConflict) {
            session()->setFlashdata('content_slug_warning', __('content.category_slug_tag_conflict', 'This slug also exists as a tag. The category will take priority in URL resolution.'));
        }

        $this->model->save(array_merge($data, ['id' => $id]));
        Resolver::flushContentCache();
        return $this->redirectWith(route_to('admin.content.categories'), __('content.saved', 'Saved'));
    }

    public function destroy(string $hash): \CodeIgniter\HTTP\RedirectResponse
    {
        $this->model->delete(signedId($hash));
        Resolver::flushContentCache();
        return $this->redirectWith(route_to('admin.content.categories'), __('content.deleted', 'Deleted'));
    }

    private function wouldCreateCycle(int $currentId, int $newParentId): bool
    {
        $id   = $newParentId;
        $seen = [];
        while ($id) {
            if ($id === $currentId) return true;
            if (isset($seen[$id])) return true;
            $seen[$id] = true;
            $cat = $this->model->getById($id);
            $id  = $cat->parent_id ?? null;
        }
        return false;
    }
}
