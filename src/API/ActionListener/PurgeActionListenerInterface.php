<?php

declare(strict_types=1);

namespace App\API\ActionListener;

use App\API\FileInterface;

interface PurgeActionListenerInterface
{
    public function onDelete(FileInterface $file): void;
}
