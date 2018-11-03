<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\File;
use App\Exception\NotificationDeliveryFailureException;
use Swift_Mailer;
use Swift_Message;
use Twig_Environment;

class NotificationService implements NotificationServiceInterface
{
    private const BLOCK_SUBJECT = 'subject';
    private const BLOCK_BODY = 'body';

    /** @var \Swift_Mailer */
    private $mailer;

    /** @var \Twig_Environment */
    private $twig;

    /** @var string */
    private $emailTemplate;

    /**
     * @param \Swift_Mailer $mailer
     * @param \Twig_Environment $twig
     * @param string $emailTemplate
     */
    public function __construct(Swift_Mailer $mailer, Twig_Environment $twig, string $emailTemplate)
    {
        $this->mailer = $mailer;
        $this->twig = $twig;
        $this->emailTemplate = $emailTemplate;
    }

    /**
     * {@inheritdoc}
     */
    public function notify(File $file, array $recipients): void
    {
        $template = $this->twig->loadTemplate($this->emailTemplate);
        $context = [
            'file' => $file,
        ];

        $message = new Swift_Message();
        $message->setSubject($template->renderBlock(self::BLOCK_SUBJECT, $context));
        $message->setBody($template->renderBlock(self::BLOCK_BODY, $context), 'text/html');
        $message->setTo($recipients);

        $this->mailer->send($message, $failedRecipients);

        if (!empty($failedRecipients)) {
            throw new NotificationDeliveryFailureException($failedRecipients);
        }
    }
}
