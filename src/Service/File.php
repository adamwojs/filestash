<?php

declare(strict_types=1);

namespace App\Service;

use DateTimeInterface;
use App\Entity\File as FileEntity;

final class File implements FileInterface
{
    /** @var \App\Entity\File */
    private $entity;

    /** @var \App\Service\FileSystemProxy */
    private $fileSystemProxy;

    /**
     * @param \App\Entity\File $entity
     * @param \App\Service\FileSystemProxy $fileSystemProxy
     */
    public function __construct(FileEntity $entity, FileSystemProxy $fileSystemProxy)
    {
        $this->entity = $entity;
        $this->fileSystemProxy = $fileSystemProxy;
    }

    /**
     * {@inheritdoc}
     */
    public function getPath(): string
    {
        return $this->entity->getPath();
    }

    /**
     * {@inheritdoc}
     */
    public function getMimeType(): string
    {
        return $this->entity->getMimeType();
    }

    /**
     * {@inheritdoc}
     */
    public function getSize(): int
    {
        return $this->entity->getSize();
    }

    /**
     * {@inheritdoc}
     */
    public function hasExpiresDate(): bool
    {
        return null !== $this->entity->getExpiresAt();
    }

    /**
     * {@inheritdoc}
     */
    public function getExpiresAt(): ?DateTimeInterface
    {
        return $this->entity->getExpiresAt();
    }

    /**
     * {@inheritdoc}
     */
    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->entity->getCreatedAt();
    }

    /**
     * {@inheritdoc}
     */
    public function hasDownloadLimit(): bool
    {
        return $this->entity->hasDownloadLimit();
    }

    /**
     * {@inheritdoc}
     */
    public function getMaxDownloads(): ?int
    {
        return $this->entity->getMaxDownloads();
    }

    /**
     * {@inheritdoc}
     */
    public function getDataStream()
    {
        return $this->fileSystemProxy->getReadStream($this->entity);
    }
}
