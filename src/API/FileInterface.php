<?php

namespace App\API;

use DateTimeInterface;

interface FileInterface
{
    public function getPath(): string;

    public function getMimeType(): string;

    public function getSize(): int;

    public function hasExpiresDate(): bool;

    public function getExpiresAt(): ?DateTimeInterface;

    public function getCreatedAt(): ?DateTimeInterface;

    public function hasDownloadLimit(): bool;

    public function getMaxDownloads(): ?int;

    public function getDataStream();
}
