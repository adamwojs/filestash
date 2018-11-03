<?php

namespace App\Service;

use App\Entity\File;

interface DownloadLogServiceInterface
{
    /**
     * Created download log entry for given file.
     *
     * @param \App\Entity\File $file
     */
    public function create(File $file): void;

    /**
     * Returns number of the downloads for given file.
     *
     * @param \App\Entity\File $file
     * @return int
     */
    public function getDownloadsCount(File $file): int;
}
