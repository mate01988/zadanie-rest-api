<?php declare(strict_types=1);

namespace App\Service;


use App\Entity\User;
use App\Entity\UserToken;
use App\Repository\UserRepository;
use App\Service\Interfaces\UserServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Class UserService
 * @package App\Service
 */
class UserService implements UserServiceInterface
{

    private $em;
    private $passwordEncoder;
    private $userRepository;

    /**
     * UserService constructor.
     * @param EntityManagerInterface $em
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param UserRepository $userRepository
     */
    public function __construct(EntityManagerInterface $em,
                                UserPasswordEncoderInterface $passwordEncoder,
                                UserRepository $userRepository)
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->em = $em;
        $this->userRepository = $userRepository;
    }

    /**
     * @param User $user
     * @return User
     * @throws \Exception
     */
    public function login(User $user): User
    {
        $token = md5(rand(99, 99999) . time());

        $userToken = new UserToken();
        $userToken->setToken($token);
        $userToken->setUser($user);

        $this->em->persist($userToken);
        $this->em->flush();

        return $user;
    }

    /**
     * @param User $user
     * @return User
     * @throws \Exception
     */
    public function create(User $user): User
    {
        $password = $this->passwordEncoder->encodePassword($user, $user->getPassword());
        $user->setPassword($password);
        $user->setCreatedAt(new \DateTime());
        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }

}