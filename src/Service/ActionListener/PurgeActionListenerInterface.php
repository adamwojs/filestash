<?php

declare(strict_types=1);

namespace App\Service\ActionListener;

use App\Service\FileInterface;

interface PurgeActionListenerInterface
{
    public function onDelete(FileInterface $file): void;
}
