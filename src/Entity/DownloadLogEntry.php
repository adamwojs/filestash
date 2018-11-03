<?php

declare(strict_types=1);

namespace App\Entity;

use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="\App\Repository\DownloadLogRepository")
 * @ORM\Table(name="download_log")
 */
class DownloadLogEntry
{
    /**
     * @var int|null
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var \App\Entity\File|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\File")
     * @ORM\JoinColumn(name="file_id", referencedColumnName="id")
     */
    private $file;

    /**
     * @var \DateTimeInterface
     *
     * @ORM\Column(type="datetime_immutable")
     */
    private $created;

    public function __construct(File $file)
    {
        $this->created = new DateTimeImmutable();
        $this->file = $file;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFile(): ?File
    {
        return $this->file;
    }

    public function getCreated(): DateTimeInterface
    {
        return $this->created;
    }
}
