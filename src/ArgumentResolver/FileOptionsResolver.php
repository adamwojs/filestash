<?php

declare(strict_types=1);

namespace App\ArgumentResolver;

use App\API\FileOptions;
use DateInterval;
use Generator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class FileOptionsResolver implements ArgumentValueResolverInterface
{
    private const HEADER_TTL = 'x-ttl';
    private const HEADER_MAX_DOWNLOADS = 'x-max-downloads';
    private const HEADER_NOTIFY = 'x-notify';

    /**
     * {@inheritdoc}
     */
    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        return FileOptions::class === $argument->getType();
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(Request $request, ArgumentMetadata $argument): Generator
    {
        $ttl = null;
        if ($request->headers->has(self::HEADER_TTL)) {
            $ttl = new DateInterval(sprintf('P%dD', (int)$request->headers->get(self::HEADER_TTL)));
        }

        $maxDownloads = null;
        if ($request->headers->has(self::HEADER_MAX_DOWNLOADS)) {
            $maxDownloads = (int)$request->headers->get(self::HEADER_MAX_DOWNLOADS);
        }

        $recipients = (array)$request->headers->get(self::HEADER_NOTIFY, []);
        $recipients = array_map('trim', $recipients);
        $recipients = array_filter($recipients, function ($recipient) {
            return !empty($recipient);
        });

        return yield new FileOptions($ttl, $maxDownloads, $recipients);
    }
}
