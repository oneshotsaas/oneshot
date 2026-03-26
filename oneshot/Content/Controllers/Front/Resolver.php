<?php

namespace OneShot\Content\Controllers\Front;

use OneShot\Core\Controllers\Front;
use OneShot\Content\Services\Resolver as ResolverService;

class Resolver extends Front
{
    public function resolve(): string|\CodeIgniter\HTTP\RedirectResponse
    {
        helper('content');

        // Use the full URI path so multi-segment paths like /blog/ai/post work correctly.
        // Passing (:any) via $1 would split on '/' into separate method arguments.
        $path = ltrim($this->request->getUri()->getPath(), '/');

        $resolver = new ResolverService();
        $result   = $resolver->resolve($path);

        if (!$result) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $full = $resolver->loadFull($result->kind, $result->data['id']);
        if (!$full) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // Whitelist of allowed template names — prevents path traversal from DB values
        static $allowed = [
            'item'     => ['post', 'page'],
            'category' => ['category'],
            'tag'      => ['tag'],
        ];

        switch ($result->kind) {
            case 'item':
                // Canonical redirect — path must be relative (no scheme/host)
                $canonical = $resolver->buildCanonicalPath($result->data);
                if (!preg_match('#^/[a-z0-9/_-]*$#', $canonical)) {
                    throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
                }
                $current = '/' . ltrim($path, '/');
                if ($current !== $canonical) {
                    return redirect()->to($canonical, 301);
                }
                $this->setMeta($full->meta_title ?: $full->title, $full->meta_description ?? '');
                $template = in_array($full->template, $allowed['item'], true) ? $full->template : 'post';
                return $this->render("Content::front/{$template}", ['item' => $full, 'resolver' => $resolver]);

            case 'category':
                $this->setMeta($full->meta_title ?: $full->title, $full->meta_description ?? '');
                $template = in_array($full->template, $allowed['category'], true) ? $full->template : 'category';
                return $this->render("Content::front/{$template}", ['category' => $full, 'resolver' => $resolver]);

            case 'tag':
                $this->setMeta($full->meta_title ?: $full->title, $full->meta_description ?? '');
                $template = in_array($full->template, $allowed['tag'], true) ? $full->template : 'tag';
                return $this->render("Content::front/{$template}", ['tag' => $full, 'resolver' => $resolver]);
        }

        throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
    }
}
