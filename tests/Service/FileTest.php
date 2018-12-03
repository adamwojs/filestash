<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\File as FileEntity;
use App\Service\File;
use App\Service\FileSystemProxy;
use DateTime;
use PHPUnit\Framework\TestCase;

class FileTest extends TestCase
{
    /** @var \App\Entity\File|\PHPUnit\Framework\MockObject\MockObject */
    private $entity;

    /** @var \App\Service\FileSystemProxy|\PHPUnit\Framework\MockObject\MockObject */
    private $fileSystemProxy;

    /** @var \App\Service\File */
    private $file;

    protected function setUp(): void
    {
        $this->entity = $this->createMock(FileEntity::class);
        $this->fileSystemProxy = $this->createMock(FileSystemProxy::class);
        $this->file = new File($this->entity, $this->fileSystemProxy);
    }

    /**
     * @covers \App\Service\File::getPath
     */
    public function testGetPath(): void
    {
        $this->isDelegatedToEntity('getPath', '/var/data/document.pdf');
    }

    /**
     * @covers \App\Service\File::getMimeType
     */
    public function testGetMimeType(): void
    {
        $this->isDelegatedToEntity('getMimeType', 'text/html');
    }

    /**
     * @covers \App\Service\File::getSize
     */
    public function testGetSize(): void
    {
        $this->isDelegatedToEntity('getSize', 0xFFFF);
    }

    /**
     * @covers \App\Service\File::hasExpiresDate
     */
    public function testHasExpiresDate(): void
    {
        $this->entity
            ->expects($this->any())
            ->method('getExpiresAt')
            ->willReturn($this->createMock(DateTime::class), null);

        $this->assertTrue($this->file->hasExpiresDate());
        $this->assertFalse($this->file->hasExpiresDate());
    }

    /**
     * @covers \App\Service\File::getExpiresAt
     */
    public function testGetExpiresAt(): void
    {
        $this->isDelegatedToEntity('getExpiresAt', $this->createMock(DateTime::class));
    }

    /**
     * @covers \App\Service\File::getCreatedAt
     */
    public function testGetCreatedAt(): void
    {
        $this->isDelegatedToEntity('getCreatedAt', $this->createMock(DateTime::class));
    }

    /**
     * @covers \App\Service\File::hasDownloadLimit
     */
    public function testHasDownloadLimit(): void
    {
        $this->isDelegatedToEntity('hasDownloadLimit', true);
    }

    /**
     * @covers \App\Service\File::getMaxDownloads
     */
    public function testGetMaxDownloads(): void
    {
        $this->isDelegatedToEntity('getMaxDownloads', 0xFFFF);
    }

    /**
     * @covers \App\Service\File::getDataStream
     */
    public function testGetDataStream(): void
    {
        $stream = null;

        $this->fileSystemProxy
            ->expects($this->once())
            ->method('getReadStream')
            ->with($this->entity)
            ->willReturn($stream);

        $this->assertEquals($stream, $this->file->getDataStream());
    }

    private function isDelegatedToEntity(string $method, $value): void
    {
        $this->entity
            ->expects($this->once())
            ->method($method)
            ->willReturn($value);

        $this->assertEquals($value, $this->file->{$method}());
    }
}
