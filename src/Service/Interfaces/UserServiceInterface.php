<?php declare(strict_types=1);

namespace App\Service\Interfaces;


use App\Entity\User;

/**
 * Interface UserServiceInterface
 * @package App\Service\Interfaces
 */
interface UserServiceInterface
{

    public function create(User $user);

}