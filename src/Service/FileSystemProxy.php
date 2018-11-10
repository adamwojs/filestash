<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\File;

interface FileSystemProxy
{
    /**
     * Retrieves a read-stream for a file.
     *
     * @param \App\Entity\File $file
     * @return resource
     */
    public function getReadStream(File $file);
}
