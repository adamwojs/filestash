<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserService implements UserServiceInterface
{
    /** @var \Doctrine\ORM\EntityManagerInterface */
    private $em;

    /** @var \App\Repository\UserRepository */
    private $repository;

    /** @var \Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface */
    private $passwordEncoder;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface                                  $em
     * @param \Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface $passwordEncoder
     */
    public function __construct(EntityManagerInterface $em, UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->em = $em;
        $this->repository = $em->getRepository(User::class);
        $this->passwordEncoder = $passwordEncoder;
    }

    /**
     * {@inheritdoc}
     */
    public function create(string $username, string $plainPassword, string $email): void
    {
        if (!$username) {
            throw new InvalidArgumentException('Username can not be empty');
        }

        if (!$this->isUniqueUsername($username)) {
            throw new InvalidArgumentException("Duplicated username: $username");
        }

        if (!$email) {
            throw new InvalidArgumentException('E-mail can not be empty');
        }

        if (!$this->isUniqueEmail($email)) {
            throw new InvalidArgumentException("Duplicated e-mail: $email");
        }

        if (!$plainPassword) {
            throw new InvalidArgumentException('Password can not be empty');
        }

        $user = new User();

        $encodedPassword = $this->passwordEncoder->encodePassword($user, $plainPassword);

        $user->setUsername($username);
        $user->setPassword($encodedPassword);
        $user->setEmail($email);

        $this->em->persist($user);
        $this->em->flush();
    }

    private function isUniqueUsername(string $username): bool
    {
        return null === $this->repository->findOneBy([
            'username' => $username,
        ]);
    }

    private function isUniqueEmail(string $email): bool
    {
        return null === $this->repository->findOneBy([
            'email' => $email,
        ]);
    }
}
