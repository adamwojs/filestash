<?php

declare(strict_types=1);

namespace App\Exception;

use Exception;
use Throwable;

class FileDownloadLimitException extends Exception
{
    /** @var string */
    private $id;

    public function __construct(string $id, $code = 0, Throwable $previous = null)
    {
        $this->id = $id;

        parent::__construct("File $id download limit exceeded", $code, $previous);
    }

    public function getId(): string
    {
        return $this->id;
    }
}
