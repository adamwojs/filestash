<?php

namespace App\Service;

interface FileServiceInterface
{
    /**
     * @param string $filename
     * @param resource $resource
     * @param \App\Service\FileOptions $options
     *
     * @return string
     */
    public function save(string $filename, $resource, FileOptions $options): string;

    /**
     * @param string $id
     *
     * @throws \App\Exception\FileNotFoundException
     * @throws \App\Exception\FileDownloadLimitException
     *
     * @return resource
     */
    public function getContent(string $id);
}
