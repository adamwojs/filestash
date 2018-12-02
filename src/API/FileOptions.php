<?php

declare(strict_types=1);

namespace App\API;

use DateInterval;

final class FileOptions
{
    /** @var DateInterval|null */
    private $ttl;

    /** @var int|null */
    private $maxDownloads;

    /** @var array */
    private $recipients;

    /**
     * @param DateInterval|null $ttl
     * @param int|null $maxDownloads
     * @param array $recipients
     */
    public function __construct(?DateInterval $ttl = null, ?int $maxDownloads = null, array $recipients = [])
    {
        $this->ttl = $ttl;
        $this->maxDownloads = $maxDownloads;
        $this->recipients = $recipients;
    }

    public function hasTtl(): bool
    {
        return null !== $this->ttl;
    }

    public function getTtl(): ?DateInterval
    {
        return $this->ttl;
    }

    public function hasMaxDownloads(): bool
    {
        return $this->maxDownloads > 0;
    }

    public function getMaxDownloads(): ?int
    {
        return $this->maxDownloads;
    }

    public function hasRecipients(): bool
    {
        return !empty($this->recipients);
    }

    public function getRecipients(): array
    {
        return $this->recipients;
    }
}
