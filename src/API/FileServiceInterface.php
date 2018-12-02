<?php

namespace App\API;

use App\API\ActionListener\PurgeActionListenerInterface;

interface FileServiceInterface
{
    /**
     * @param string $id
     *
     * @throws \App\Exception\FileNotFoundException
     * @throws \App\Exception\FileDownloadLimitException
     *
     * @return \App\API\FileInterface
     */
    public function load(string $id): FileInterface;

    /**
     * @param string $filename
     * @param resource $resource
     * @param \App\API\FileOptions $options
     *
     * @return string
     */
    public function save(string $filename, $resource, FileOptions $options): string;

    /**
     * @param \App\API\ActionListener\PurgeActionListenerInterface|null $logger
     */
    public function purge(PurgeActionListenerInterface $logger = null): void;
}
