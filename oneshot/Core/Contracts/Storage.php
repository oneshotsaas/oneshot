<?php

namespace OneShot\Core\Contracts;

interface Storage
{
    public function upload(string $localPath, string $remotePath, array $options = []): string;
    public function delete(string $remotePath): bool;
    public function url(string $remotePath): string;
}
