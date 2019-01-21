<?php declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\User;
use App\Form\LoginType;
use App\Form\UserCreateType;
use App\Repository\UserRepository;
use App\Service\Interfaces\UserServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;


/**
 * @Route("/api", name="register")
 */
class UserController extends BaseController
{

    /**
     * User login
     *
     * @Route("/login", name="auth_login", methods={"POST"})
     *
     *
     * @param Request $request
     * @param UserPasswordEncoderInterface $encoder
     * @param EntityManagerInterface $em
     * @param UserServiceInterface $userService
     * @param UserRepository $userRepository
     *
     * @return JsonResponse
     * @throws \Exception
     */
    public function login(Request $request,
                          UserPasswordEncoderInterface $encoder,
                          EntityManagerInterface $em,
                          UserServiceInterface $userService,
                          UserRepository $userRepository): JsonResponse
    {
        $user = new User();
        $form = $this->createForm(LoginType::class, $user);

        $form->submit($request->request->all());

        if ($form->isSubmitted() && $form->isValid()) {

            $user = $userRepository->findOneBy(['email' => $user->getEmail()]);

            if (null === $user) {
                return $this->json("This user not exists.", 401);
            }

            if (false === $encoder->isPasswordValid($user, $form->get('password')->getData())) {
                return $this->responseJsonError("Incorrect login or password.", 401);
            }

            try {

                $responseData = $userService->login($user);

            } catch (\Exception $e) {
                return $this->responseJsonError($e->getMessage(), 400);
            }


            $response = $this->responseJson([
                'data' => $responseData
            ], ['groups' => ['user', 'userAuth']]);


            return $response;

        } else {
            return $this->json([
                'status' => self::RESPONSE_ERROR,
                'message' =>
                    [
                        'form' => $this->getErrorsFromForm($form),
                        'fields' => $this->getErrorsFromFormFields($form)
                    ]
            ], 422);
        }
    }

    /**
     * @Route("/register", name="register", methods={"POST"})
     */
    public function register(Request $request, UserServiceInterface $userService): JsonResponse
    {
        $user = new User();
        $form = $this->createForm(UserCreateType::class, $user);

        $form->submit($request->request->all());

        if ($form->isSubmitted() && $form->isValid()) {

            try {
                /** @var User $user */
                $user = $userService->create($form->getData());

            } catch (\Exception $exception) {
                return $this->json([
                    'status' => self::RESPONSE_ERROR,
                    'message' => $exception->getMessage()
                ], 400);
            }

            return $this->json([
                'status' => self::RESPONSE_SUCCESS,
                'message' => 'The user has been registered.',
                'data' => [
                    'user' => [
                        'id' => $user->getId(),
                        'email' => $user->getEmail()
                    ]
                ]
            ], 200);
        } else {
            return $this->json([
                'status' => self::RESPONSE_ERROR,
                'message' =>
                    [
                        'form' => $this->getErrorsFromForm($form),
                        'fields' => $this->getErrorsFromFormFields($form)
                    ]
            ], 422);
        }
    }
}

