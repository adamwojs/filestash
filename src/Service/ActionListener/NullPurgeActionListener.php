<?php

declare(strict_types=1);

namespace App\Service\ActionListener;

use App\Service\FileInterface;

final class NullPurgeActionListener implements PurgeActionListenerInterface
{
    /**
     * {@inheritdoc}
     */
    public function onDelete(FileInterface $file): void
    {
        // Do nothing
    }
}
