<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\DownloadLogEntry;
use App\Entity\File;
use Doctrine\ORM\EntityManagerInterface;

class DownloadLogService implements DownloadLogServiceInterface
{
    /** @var \Doctrine\ORM\EntityManagerInterface */
    private $em;

    /** @var \App\Repository\DownloadLogRepository */
    private $repository;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->repository = $this->em->getRepository(DownloadLogEntry::class);
    }

    /**
     * {@inheritdoc}
     */
    public function create(File $file): void
    {
        $this->em->persist(new DownloadLogEntry($file));
        $this->em->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function getDownloadsCount(File $file): int
    {
        return $this->repository->count([
            'file' => $file,
        ]);
    }
}
