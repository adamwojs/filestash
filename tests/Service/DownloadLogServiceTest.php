<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\DownloadLogEntry;
use App\Entity\File;
use App\Service\DownloadLogService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;

class DownloadLogServiceTest extends TestCase
{
    /** @var \Doctrine\ORM\EntityManagerInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $em;

    /** @var \Doctrine\ORM\EntityRepository|\PHPUnit\Framework\MockObject\MockObject */
    private $repository;

    /** @var \App\Service\DownloadLogService */
    private $service;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(EntityRepository::class);
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->em
            ->expects($this->any())
            ->method('getRepository')
            ->with(DownloadLogEntry::class)
            ->willReturn($this->repository);

        $this->service = new DownloadLogService($this->em);
    }

    /**
     * @covers \App\Service\DownloadLogService::create
     */
    public function testCreate(): void
    {
        $file = $this->createMock(File::class);

        $this->em
            ->expects($this->once())
            ->method('persist')
            ->willReturn(function ($entry) use ($file) {
                $this->assertInstanceOf(DownloadLogEntry::class, $entry);
                $this->assertEquals($file, $entry->getFile());
            });

        $this->em
            ->expects($this->once())
            ->method('flush');

        $this->service->create($file);
    }

    /**
     * @covers \App\Service\DownloadLogService::getDownloadsCount
     */
    public function testCount(): void
    {
        $downloadCounter = PHP_INT_MAX;

        $file = $this->createMock(File::class);

        $this->repository
            ->expects($this->once())
            ->method('count')
            ->with(['file' => $file])
            ->willReturn($downloadCounter);

        $this->assertEquals($downloadCounter, $this->service->getDownloadsCount($file));
    }
}
