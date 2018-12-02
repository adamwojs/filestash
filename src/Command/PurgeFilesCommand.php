<?php

declare(strict_types=1);

namespace App\Command;

use App\API\ActionListener\PurgeActionListenerInterface;
use App\API\FileInterface;
use App\API\FileServiceInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PurgeFilesCommand extends Command
{
    use LockableTrait;

    /** @var \App\API\FileServiceInterface */
    private $fileService;

    /**
     * @param \App\API\FileServiceInterface $fileService
     */
    public function __construct(FileServiceInterface $fileService)
    {
        parent::__construct('app:file:purge');

        $this->fileService = $fileService;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        if (!$this->lock()) {
            $output->writeln('The command is already running in another process.');

            return;
        }

        $this->fileService->purge($this->createFileDeleteActionListener($output));

        $this->release();
    }

    private function createFileDeleteActionListener(OutputInterface $output): PurgeActionListenerInterface
    {
        return new class($output) implements PurgeActionListenerInterface {
            /** @var \Symfony\Component\Console\Output\OutputInterface */
            private $output;

            public function __construct(OutputInterface $output)
            {
                $this->output = $output;
            }

            /**
             * {@inheritdoc}
             */
            public function onDelete(FileInterface $file): void
            {
                $this->output->writeln("Deleting {$file->getPath()}");
            }
        };
    }
}
