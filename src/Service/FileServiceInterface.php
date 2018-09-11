<?php

namespace App\Service;

use App\Exception\FileNotFoundException;

interface FileServiceInterface
{
    public function save(string $filename, $resource, array $options = []): string;

    /**
     * @param string $id
     *
     * @throws \App\Exception\FileNotFoundException
     *
     * @return resource
     */
    public function load(string $id);
}
