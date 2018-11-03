<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\File;
use App\Exception\FileNotFoundException;
use DateInterval;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use League\Flysystem\FilesystemInterface;
use Ramsey\Uuid\Uuid;

class FileService implements FileServiceInterface
{
    public const OPTION_TTL = 'ttl';
    public const OPTION_MAX_DOWNLOADS = 'max_downloads';
    public const OPTION_NOTIFY = 'notify';

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
    public function save(string $filename, $resource, array $options = []): string
    {
        $path = Uuid::uuid1()->toString();

        $this->filesystem->writeStream($path, $resource);

        $file = new File($path);
        $file->setPath($path);
        $file->setSize($this->filesystem->getSize($path));
        $file->setMimeType($this->filesystem->getMimetype($path));

        if (isset($options[self::OPTION_TTL]) && $options[self::OPTION_TTL] > 0) {
            $expiresAt = new DateTime();
            $expiresAt->setTimestamp($file->getCreated()->getTimestamp());
            $expiresAt->add(new DateInterval(sprintf('P%dD', (int) $options[self::OPTION_TTL])));

            $file->setExpiresAt($expiresAt);
        }

        if (isset($options[self::OPTION_MAX_DOWNLOADS])) {
            $file->setMaxDownloads((int) $options[self::OPTION_MAX_DOWNLOADS]);
        }

        try {
            $this->em->persist($file);
            $this->em->flush();

            if (isset($options[self::OPTION_NOTIFY])) {
                $this->notificationService->notify($file, $options[self::OPTION_NOTIFY]);
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
