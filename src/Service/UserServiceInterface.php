<?php

namespace App\Service;

interface UserServiceInterface
{
    public function create(string $username, string $plainPassword, string $email): void;
}
