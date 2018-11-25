<?php

namespace App\Service;

use App\Service\ActionListener\PurgeActionListenerInterface;

interface FileServiceInterface
{
    /**
     * @param string $id
     *
     * @throws \App\Exception\FileNotFoundException
     * @throws \App\Exception\FileDownloadLimitException
     *
     * @return \App\Service\FileInterface
     */
    public function load(string $id): FileInterface;

    /**
     * @param string $filename
     * @param resource $resource
     * @param \App\Service\FileOptions $options
     *
     * @return string
     */
    public function save(string $filename, $resource, FileOptions $options): string;

    /**
     * @param \App\Service\ActionListener\PurgeActionListenerInterface|null $logger
     */
    public function purge(PurgeActionListenerInterface $logger = null): void;
}
