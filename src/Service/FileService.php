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
    /** @var \League\Flysystem\FilesystemInterface */
    private $filesystem;

    /** @var \Doctrine\ORM\EntityManagerInterface */
    private $em;

    /** @var \App\Repository\FileRepository */
    private $repository;

    /**
     * @param \League\Flysystem\FilesystemInterface $filesystem
     * @param \Doctrine\ORM\EntityManagerInterface  $em
     */
    public function __construct(FilesystemInterface $filesystem, EntityManagerInterface $em)
    {
        $this->filesystem = $filesystem;
        $this->em = $em;
        $this->repository = $em->getRepository(File::class);
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

        if (isset($options['ttl']) && $options['ttl'] > 0) {
            $expiresAt = new DateTime();
            $expiresAt->setTimestamp($file->getCreated()->getTimestamp());
            $expiresAt->add(new DateInterval(sprintf("P%dD", (int) $options['ttl'])));

            $file->setExpiresAt($expiresAt);
        }

        if (isset($options['max_downloads'])) {
            $file->setMaxDownloads((int) $options['max_downloads']);
        }

        try {
            $this->em->persist($file);
            $this->em->flush();
        } catch (Exception $e) {
            $this->filesystem->delete($path);
        }

        return $path;
    }

    /**
     * {@inheritdoc}
     */
    public function load(string $id)
    {
        $file = $this->repository->find($id);
        if (null === $file) {
            throw new FileNotFoundException($id);
        }

        if ($file->getExpiresAt() !== null && $file->getExpiresAt() > new DateTime()) {
            throw new FileNotFoundException($id);
        }

        return $this->filesystem->readStream($file->getPath());
    }
}
