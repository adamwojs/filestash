<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\File;
use App\Exception\FileNotFoundException;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use League\Flysystem\FilesystemInterface;
use Ramsey\Uuid\Uuid;

class FileService implements FileServiceInterface
{
    /** @var \League\Flysystem\FilesystemInterface */
    private $filesystem;

    /** @var \Doctrine\ORM\EntityManagerInterface */
    private $em;

    /** @var \App\Repository\FileRepository */
    private $repository;

    /** @var \App\Service\NotificationServiceInterface */
    private $notificationService;

    /**
     * @param \League\Flysystem\FilesystemInterface $filesystem
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param NotificationServiceInterface $notificationService
     */
    public function __construct(
        FilesystemInterface $filesystem,
        EntityManagerInterface $em,
        NotificationServiceInterface $notificationService)
    {
        $this->filesystem = $filesystem;
        $this->em = $em;
        $this->repository = $em->getRepository(File::class);
        $this->notificationService = $notificationService;
    }

    /**
     * {@inheritdoc}
     */
    public function save(string $filename, $resource, FileOptions $options): string
    {
        $path = Uuid::uuid1()->toString();

        $this->filesystem->writeStream($path, $resource);

        $file = new File($path);
        $file->setPath($path);
        $file->setSize($this->filesystem->getSize($path));
        $file->setMimeType($this->filesystem->getMimetype($path));

        if ($options->hasTtl()) {
            $expiresAt = new DateTime();
            $expiresAt->setTimestamp($file->getCreated()->getTimestamp());
            $expiresAt->add($options->getTtl());

            $file->setExpiresAt($expiresAt);
        }

        if ($options->hasMaxDownloads()) {
            $file->setMaxDownloads($options->getMaxDownloads());
        }

        try {
            $this->em->persist($file);
            $this->em->flush();

            if ($options->hasRecipients()) {
                $this->notificationService->notify($file, $options->getRecipients());
            }
        } catch (Exception $e) {
            $this->filesystem->delete($path);
            throw $e;
        }

        return $path;
    }

    /**
     * {@inheritdoc}
     */
    public function getContent(string $id)
    {
        $file = $this->repository->find($id);
        if (null === $file) {
            throw new FileNotFoundException($id);
        }

        if (null !== $file->getExpiresAt() && $file->getExpiresAt() > new DateTime()) {
            throw new FileNotFoundException($id);
        }

        return $this->filesystem->readStream($file->getPath());
    }
}
