<?php

declare(strict_types=1);

namespace App\Exception;

use Exception;
use Throwable;

class FileNotFoundException extends Exception
{
    /** @var string */
    private $id;

    public function __construct(string $id, $code = 0, Throwable $previous = null)
    {
        $this->id = $id;

        parent::__construct("File $id not found", $code, $previous);
    }

    public function getId(): string
    {
        return $this->id;
    }
}
