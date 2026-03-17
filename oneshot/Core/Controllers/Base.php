<?php

namespace OneShot\Core\Controllers;

abstract class Base extends \CodeIgniter\Controller
{
    protected array $viewData = [];
    protected string $layout  = '';
    protected array $breadcrumbs = [];

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);
    }

    protected function render(string $view, array $data = []): string
    {
        $data = array_merge($this->viewData, $data);
        $data['breadcrumbs'] = $this->breadcrumbs;

        $content = render($view, $data);

        if ($this->layout) {
            return render($this->layout, ['content' => $content] + $data);
        }

        return $content;
    }

    protected function share(string $key, mixed $val): void
    {
        $this->viewData[$key] = $val;
    }

    protected function appendBC(string $label, string $url = ''): void
    {
        $this->breadcrumbs[] = ['label' => $label, 'url' => $url];
    }

    protected function redirectWith(string $route, string $msg = '', string $type = 'success'): \CodeIgniter\HTTP\RedirectResponse
    {
        if ($msg !== '') {
            session()->setFlashdata($type, $msg);
        }

        return redirect()->to($route);
    }

}
