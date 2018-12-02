<?php

declare(strict_types=1);

namespace App\Command;

use App\API\UserServiceInterface;
use InvalidArgumentException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\SymfonyQuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class CreateUserCommand extends Command
{
    private const ARG_USERNAME = 'username';
    private const ARG_PASSWORD = 'password';
    private const ARG_EMAIL = 'email';

    /** @var \App\API\UserServiceInterface */
    private $userService;

    /**
     * @param \App\API\UserServiceInterface $userService
     */
    public function __construct(UserServiceInterface $userService)
    {
        parent::__construct('app:user:create');

        $this->userService = $userService;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this->setDescription('Creates a user.');

        $this->addArgument(self::ARG_USERNAME, null, 'The username');
        $this->addArgument(self::ARG_EMAIL, null, 'The email');
        $this->addArgument(self::ARG_PASSWORD, null, 'The password');
    }

    /**
     * {@inheritdoc}
     */
    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        $questions = [];

        if (!$input->getArgument(self::ARG_USERNAME)) {
            $question = new Question('Please choose a username');
            $question->setValidator(function ($username) {
                $username = trim((string) $username);
                if (!$username) {
                    throw new InvalidArgumentException('Username can not be empty');
                }

                return $username;
            });

            $questions[self::ARG_USERNAME] = $question;
        }

        if (!$input->getArgument(self::ARG_EMAIL)) {
            $question = new Question('Please choose an email');
            $question->setValidator(function ($email) {
                $email = trim((string) $email);
                if (!$email) {
                    throw new InvalidArgumentException('E-mail can not be empty');
                }

                return $email;
            });

            $questions[self::ARG_EMAIL] = $question;
        }

        if (!$input->getArgument(self::ARG_PASSWORD)) {
            $question = new Question('Please choose a password');
            $question->setHidden(true);
            $question->setValidator(function ($password) {
                $password = trim((string) $password);
                if (!$password) {
                    throw new InvalidArgumentException('Password can not be empty');
                }

                return $password;
            });

            $questions[self::ARG_PASSWORD] = $question;
        }

        $helper = new SymfonyQuestionHelper();
        foreach ($questions as $argument => $question) {
            $input->setArgument($argument, $helper->ask($input, $output, $question));
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $username = $input->getArgument(self::ARG_USERNAME);
        $password = $input->getArgument(self::ARG_PASSWORD);
        $email = $input->getArgument(self::ARG_EMAIL);

        $this->userService->create($username, $password, $email);

        $output->writeln(sprintf('User <comment>%s</comment> has been created.', $username));
    }
}
