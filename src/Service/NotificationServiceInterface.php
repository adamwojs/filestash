<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\File;

interface NotificationServiceInterface
{
    /**
     * Notifies given $recipients about file.
     *
     * @param \App\Entity\File $file
     * @param array $recipients
     */
    public function notify(File $file, array $recipients): void;
}
