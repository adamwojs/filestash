<?php

namespace App\Service;

interface FileServiceInterface
{
    /**
     * @param string $filename
     * @param resource $resource
     * @param array $options
     *
     * @return string
     */
    public function save(string $filename, $resource, array $options = []): string;

    /**
     * @param string $id
     *
     * @throws \App\Exception\FileNotFoundException
     *
     * @return resource
     */
    public function getContent(string $id);
}
