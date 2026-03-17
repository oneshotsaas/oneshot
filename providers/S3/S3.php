<?php

namespace Providers\S3;

use OneShot\Core\Contracts\Storage;

class S3 implements Storage
{
    public function __construct(
        private string $key    = '',
        private string $secret = '',
        private string $bucket = '',
        private string $region = ''
    ) {
        $this->key    = $key    ?: env('AWS_ACCESS_KEY_ID', '');
        $this->secret = $secret ?: env('AWS_SECRET_ACCESS_KEY', '');
        $this->bucket = $bucket ?: env('AWS_S3_BUCKET', '');
        $this->region = $region ?: env('AWS_REGION', 'us-east-1');
    }

    public function upload(string $localPath, string $remotePath, array $options = []): string
    {
        // TODO: implement S3 upload
        return '';
    }

    public function delete(string $remotePath): bool
    {
        // TODO: implement S3 delete
        return false;
    }

    public function url(string $remotePath): string
    {
        return "https://{$this->bucket}.s3.{$this->region}.amazonaws.com/{$remotePath}";
    }
}
