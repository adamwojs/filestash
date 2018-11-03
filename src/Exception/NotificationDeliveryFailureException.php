<?php

declare(strict_types=1);

namespace App\Exception;

use RuntimeException;
use Throwable;

class NotificationDeliveryFailureException extends RuntimeException
{
    /** @var array */
    private $failedRecipients;

    /**
     * @param array $failedRecipients
     * @param int $code
     * @param \Throwable|null $previous
     */
    public function __construct(array $failedRecipients, int $code = 0, Throwable $previous = null)
    {
        $this->failedRecipients = $failedRecipients;

        parent::__construct(
            'Failed to deliver notification to the following recipients: ' . implode(', ', $failedRecipients),
            $code,
            $previous
        );
    }

    /**
     * @return array An array of failures by-reference
     */
    public function getFailedRecipients(): array
    {
        return $this->failedRecipients;
    }
}
