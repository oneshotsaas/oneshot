<?php

namespace OneShot\Content\Controllers\Admin;

use OneShot\Core\Controllers\Admin;

class Upload extends Admin
{
    private string $uploadPath;

    // extension => allowed real MIME types
    private const ALLOWED_IMAGES = [
        'jpg'  => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
        'png'  => ['image/png'],
        'gif'  => ['image/gif'],
        'webp' => ['image/webp'],
    ];

    private const ALLOWED_FILES = [
        'pdf'  => ['application/pdf'],
        'doc'  => ['application/msword'],
        'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
        'xls'  => ['application/vnd.ms-excel'],
        'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
        'ppt'  => ['application/vnd.ms-powerpoint'],
        'pptx' => ['application/vnd.openxmlformats-officedocument.presentationml.presentation'],
        'odt'  => ['application/vnd.oasis.opendocument.text'],
        'ods'  => ['application/vnd.oasis.opendocument.spreadsheet'],
        'odp'  => ['application/vnd.oasis.opendocument.presentation'],
        'txt'  => ['text/plain'],
        'csv'  => ['text/csv', 'text/plain', 'application/csv'],
        'json' => ['application/json', 'text/plain'],
        'xml'  => ['application/xml', 'text/xml', 'text/plain'],
        'zip'  => ['application/zip', 'application/x-zip-compressed'],
        'gz'   => ['application/gzip', 'application/x-gzip'],
        'tar'  => ['application/x-tar'],
        'jpg'  => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
        'png'  => ['image/png'],
        'gif'  => ['image/gif'],
        'webp' => ['image/webp'],
        'svg'  => ['image/svg+xml'],
        'mp3'  => ['audio/mpeg', 'audio/mp3'],
        'mp4'  => ['video/mp4'],
        'webm' => ['video/webm'],
    ];

    public function initController($request, $response, $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->uploadPath = ROOTPATH . config('Content')->uploadPath;
        if (!is_dir($this->uploadPath)) {
            mkdir($this->uploadPath, 0755, true);
        }
    }

    public function image(): \CodeIgniter\HTTP\ResponseInterface
    {
        $file = $this->request->getFile('image');

        if (!$file || !$file->isValid()) {
            return $this->response->setJSON(['success' => 0, 'error' => ['message' => 'No file']]);
        }

        if (!$this->mimeMatches($file, self::ALLOWED_IMAGES)) {
            return $this->response->setJSON(['success' => 0, 'error' => ['message' => 'Invalid file type']]);
        }

        $name = bin2hex(random_bytes(16)) . '.' . $file->getClientExtension();
        $file->move($this->uploadPath, $name);

        return $this->response->setJSON([
            'success' => 1,
            'file'    => ['url' => base_url(config('Content')->uploadUrlPath . $name)],
        ]);
    }

    public function file(): \CodeIgniter\HTTP\ResponseInterface
    {
        $file = $this->request->getFile('file');

        if (!$file) {
            return $this->response->setJSON(['success' => 0, 'error' => ['message' => 'No file in request']]);
        }
        if (!$file->isValid()) {
            return $this->response->setJSON(['success' => 0, 'error' => ['message' => 'Upload invalid: ' . $file->getError() . ' / ' . $file->getErrorString()]]);
        }

        if (!$this->mimeMatches($file, self::ALLOWED_FILES)) {
            return $this->response->setJSON(['success' => 0, 'error' => ['message' => 'File type not allowed']]);
        }

        $name = bin2hex(random_bytes(16)) . '.' . $file->getClientExtension();
        $file->move($this->uploadPath, $name);

        return $this->response->setJSON([
            'success' => 1,
            'file'    => [
                'url'       => base_url(config('Content')->uploadUrlPath . $name),
                'name'      => $file->getClientName(),
                'size'      => $file->getSizeByUnit('b'),
                'extension' => $file->getClientExtension(),
            ],
        ]);
    }

    public function fetchUrl(): \CodeIgniter\HTTP\ResponseInterface
    {
        $url = $this->request->getPost('url');
        if (!$url || !filter_var($url, FILTER_VALIDATE_URL)) {
            return $this->response->setJSON(['success' => 0]);
        }

        $client = \Config\Services::curlrequest();
        try {
            $resp    = $client->get($url, ['timeout' => 5, 'http_errors' => false]);
            $html    = $resp->getBody();
            $title   = '';
            $desc    = '';
            $image   = '';

            if (preg_match('/<title[^>]*>(.*?)<\/title>/is', $html, $m)) {
                $title = html_entity_decode(strip_tags($m[1]), ENT_QUOTES, 'UTF-8');
            }
            if (preg_match('/<meta[^>]+name=["\']description["\'][^>]+content=["\'](.*?)["\']/is', $html, $m)) {
                $desc = html_entity_decode($m[1], ENT_QUOTES, 'UTF-8');
            }
            if (preg_match('/<meta[^>]+property=["\']og:image["\'][^>]+content=["\'](.*?)["\']/is', $html, $m)) {
                $image = $m[1];
            }

            return $this->response->setJSON([
                'success' => 1,
                'meta'    => [
                    'title'       => $title,
                    'description' => $desc,
                    'image'       => ['url' => $image],
                ],
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON(['success' => 0]);
        }
    }

    private function mimeMatches(\CodeIgniter\HTTP\Files\UploadedFile $file, array $allowedMap): bool
    {
        $ext  = strtolower($file->getClientExtension());
        $mime = $file->getClientMimeType();

        return isset($allowedMap[$ext]) && in_array($mime, $allowedMap[$ext]);
    }
}
