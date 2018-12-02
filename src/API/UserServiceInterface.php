<?php

namespace App\API;

interface UserServiceInterface
{
    public function create(string $username, string $plainPassword, string $email): void;
}
