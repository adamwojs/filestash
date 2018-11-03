<?php

declare(strict_types=1);

namespace App\Entity;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\FileRepository")
 * @ORM\Table(name="files")
 */
class File
{
    /**
     * @var string
     *
     * @ORM\Id
     * @ORM\Column(type="string")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    private $path;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    private $mimeType;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $size;

    /**
     * @var \DateTimeInterface
     *
     * @ORM\Column(type="datetime_immutable")
     */
    private $created;

    /**
     * @var \DateTimeInterface|null
     *
     * @ORM\Column(type="datetime_immutable", nullable=true)
     */
    private $expiresAt = null;

    /**
     * @var int|null
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $maxDownloads = null;

    /**
     * @param string $id
     */
    public function __construct(string $id)
    {
        $this->id = $id;
        $this->created = new DateTimeImmutable();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function setPath(string $path): void
    {
        $this->path = $path;
    }

    public function getMimeType(): string
    {
        return $this->mimeType;
    }

    public function setMimeType(string $mimeType): void
    {
        $this->mimeType = $mimeType;
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function setSize(int $size): void
    {
        $this->size = $size;
    }

    public function getCreated(): ?DateTimeInterface
    {
        return $this->created;
    }

    public function setCreated(?DateTimeInterface $created): void
    {
        if ($created instanceof DateTime) {
            $created = DateTimeImmutable::createFromMutable($created);
        }

        $this->created = $created;
    }

    public function getExpiresAt(): ?DateTimeInterface
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(?DateTimeInterface $expiresAt)
    {
        if ($expiresAt instanceof DateTime) {
            $expiresAt = DateTimeImmutable::createFromMutable($expiresAt);
        }

        $this->expiresAt = $expiresAt;
    }

    public function hasDownloadLimit(): bool
    {
        return $this->maxDownloads > 0;
    }

    public function getMaxDownloads(): ?int
    {
        return $this->maxDownloads;
    }

    public function setMaxDownloads(?int $maxDownloads): void
    {
        $this->maxDownloads = $maxDownloads;
    }
}
