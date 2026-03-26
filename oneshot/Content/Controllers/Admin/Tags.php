<?php

namespace OneShot\Content\Controllers\Admin;

use OneShot\Content\Models\Tag;
use OneShot\Content\Services\Resolver;

class Tags extends Content
{
    private Tag $model;

    public function initController($request, $response, $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->model = new Tag();
        $this->appendBC(__('content.tags', 'Tags'), route_to('admin.content.tags'));
    }

    public function index(): string
    {
        $this->share('page_actions_view', 'Content::admin/tags/_actions');
        return $this->render('Content::admin/tags/index', [
            'tags' => $this->model->where('deleted_at IS NULL')->orderBy('title')->findAll(),
        ]);
    }

    public function create(): string
    {
        $this->appendBC(__('content.new', 'New'));
        return $this->render('Content::admin/tags/form', ['tag' => null]);
    }

    public function store(): \CodeIgniter\HTTP\RedirectResponse
    {
        $data = $this->request->getPost(['title','slug','image','meta_title','meta_description','template','is_active']);
        $data['slug']      = content_slugify($data['slug'] ?: $data['title']);
        $data['is_active'] = (int)($data['is_active'] ?? 0);

        // Check conflict with root category slugs
        $catConflict = \Config\Database::connect()->table('content_categories')
            ->where('slug', $data['slug'])->where('parent_id IS NULL')->where('deleted_at IS NULL')->countAllResults() > 0;
        if ($catConflict) {
            session()->setFlashdata('content_slug_warning', __('content.tag_slug_category_conflict', 'This slug also exists as a category, which will take priority in URL resolution.'));
        }

        $this->model->add($data);
        Resolver::flushContentCache();
        return $this->redirectWith(route_to('admin.content.tags'), __('content.saved', 'Saved'));
    }

    public function edit(string $hash): string|\CodeIgniter\HTTP\RedirectResponse
    {
        $id  = signedId($hash);
        $tag = $this->model->getById($id);
        if (!$tag) {
            return $this->redirectWith(route_to('admin.content.tags'), __('content.not_found', 'Not found'), 'error');
        }
        $this->appendBC($tag->title);
        return $this->render('Content::admin/tags/form', compact('tag'));
    }

    public function update(string $hash): \CodeIgniter\HTTP\RedirectResponse
    {
        $id   = signedId($hash);
        $data = $this->request->getPost(['title','slug','image','meta_title','meta_description','template','is_active']);
        $data['slug']      = content_slugify($data['slug'] ?: $data['title']);
        $data['is_active'] = (int)($data['is_active'] ?? 0);

        $catConflict = \Config\Database::connect()->table('content_categories')
            ->where('slug', $data['slug'])->where('parent_id IS NULL')->where('deleted_at IS NULL')->countAllResults() > 0;
        if ($catConflict) {
            session()->setFlashdata('content_slug_warning', __('content.tag_slug_category_conflict', 'This slug also exists as a category, which will take priority in URL resolution.'));
        }

        $this->model->save(array_merge($data, ['id' => $id]));
        Resolver::flushContentCache();
        return $this->redirectWith(route_to('admin.content.tags'), __('content.saved', 'Saved'));
    }

    public function destroy(string $hash): \CodeIgniter\HTTP\RedirectResponse
    {
        $this->model->delete(signedId($hash));
        Resolver::flushContentCache();
        return $this->redirectWith(route_to('admin.content.tags'), __('content.deleted', 'Deleted'));
    }
}
