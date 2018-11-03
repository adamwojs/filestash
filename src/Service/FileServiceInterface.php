<?php

namespace App\Service;

interface FileServiceInterface
{
    /**
     * @param string $filename
     * @param resource $resource
     * @param FileOptions $options
     *
     * @return string
     */
    public function save(string $filename, $resource, FileOptions $options): string;

    /**
     * @param string $id
     *
     * @throws \App\Exception\FileNotFoundException
     *
     * @return resource
     */
    public function getContent(string $id);
}
