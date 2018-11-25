<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\File as FileEntity;
use App\Exception\FileDownloadLimitException;
use App\Exception\FileNotFoundException;
use App\Service\ActionListener\PurgeActionListenerInterface;
use App\Service\ActionListener\NullPurgeActionListener;
use DateTime;
use DateTimeImmutable;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use League\Flysystem\FileNotFoundException as FileSystemFileNotFoundException;
use League\Flysystem\FilesystemInterface;
use Ramsey\Uuid\Uuid;

class FileService implements FileServiceInterface, FileSystemProxy
{
    /** @var \League\Flysystem\FilesystemInterface */
    private $filesystem;

    /** @var \Doctrine\ORM\EntityManagerInterface */
    private $em;

    /** @var \App\Repository\FileRepository */
    private $repository;

    /** @var \App\Service\NotificationServiceInterface */
    private $notificationService;

    /** @var \App\Service\DownloadLogServiceInterface */
    private $downloadLogService;

    /**
     * @param \League\Flysystem\FilesystemInterface $filesystem
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \App\Service\NotificationServiceInterface $notificationService
     * @param \App\Service\DownloadLogServiceInterface $downloadLogService
     */
    public function __construct(
        FilesystemInterface $filesystem,
        EntityManagerInterface $em,
        NotificationServiceInterface $notificationService,
        DownloadLogServiceInterface $downloadLogService)
    {
        $this->filesystem = $filesystem;
        $this->em = $em;
        $this->repository = $em->getRepository(FileEntity::class);
        $this->notificationService = $notificationService;
        $this->downloadLogService = $downloadLogService;
    }

    /**
     * {@inheritdoc}
     */
    public function save(string $filename, $resource, FileOptions $options): string
    {
        $path = Uuid::uuid1()->toString();

        $this->filesystem->writeStream($path, $resource);

        $file = new FileEntity($path);
        $file->setPath($path);
        $file->setSize($this->filesystem->getSize($path));
        $file->setMimeType($this->filesystem->getMimetype($path));

        if ($options->hasTtl()) {
            $expiresAt = new DateTime();
            $expiresAt->setTimestamp($file->getCreatedAt()->getTimestamp());
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
    public function load(string $id): FileInterface
    {
        $entity = $this->repository->find($id);
        if (null === $entity) {
            throw new FileNotFoundException($id);
        }

        if (null !== $entity->getExpiresAt() && $entity->getExpiresAt() > new DateTime()) {
            throw new FileNotFoundException($id);
        }

        return new File($entity, $this);
    }

    /**
     * {@inheritdoc}
     */
    public function getReadStream(FileEntity $file)
    {
        if ($file->hasDownloadLimit()) {
            $count = $this->downloadLogService->getDownloadsCount($file);
            if ($count >= $file->getMaxDownloads()) {
                throw new FileDownloadLimitException($file->getId());
            }
        }

        $content = $this->filesystem->readStream($file->getPath());

        $this->downloadLogService->create($file);

        return $content;
    }

    /**
     * {@inheritdoc}
     */
    public function purge(PurgeActionListenerInterface $logger = null): void
    {
        if (null === $logger) {
            $logger = new NullPurgeActionListener();
        }

        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->andX(
            Criteria::expr()->neq('expiresAt', null),
            Criteria::expr()->lt('expiresAt', new DateTimeImmutable())
        ));

        /** @var \App\Entity\File[] $files */
        $files = $this->repository->matching($criteria);
        foreach ($files as $file) {
            $logger->onDelete(new File($file, $this));

            try {
                $this->filesystem->delete($file->getPath());
            } catch (FileSystemFileNotFoundException $e) {
                // File already doesn't exists
            } finally {
                $this->em->remove($file);
                $this->em->flush();
            }
        }
    }
}
