<?php declare(strict_types=1);

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Post;
use App\Form\CommentCreateType;
use App\Form\PostCreateType;
use App\Repository\PostRepository;
use App\Service\Interfaces\PostServiceInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * Class PostController
 *
 *
 * @Security("has_role('ROLE_USER')")
 *
 * @Route("/api")
 *
 * @package Api\Controller\Api
 */
class PostController extends BaseController
{

    /**
     * @param Request $request
     * @param PostRepository $postRepository
     * @return Post|null
     */
    private function getPost(Request $request, PostRepository $postRepository): ?Post
    {
        $id = $request->get('id', false);

        if (false === $id) {
            return null;
        }

        try {
            return $postRepository->find(intval($id));

        } catch (\Exception $e) {

            return null;
        }

        return null;
    }

    /**
     * Create a post
     *
     * @Route("/posts", methods={"POST"})
     *
     * @param Request $request
     * @param PostServiceInterface $postService
     *
     * @return JsonResponse
     * @throws \Exception
     */
    public function createAction(Request $request, PostServiceInterface $postService): JsonResponse
    {

        $post = new Post();
        $form = $this->createForm(PostCreateType::class, $post);

        $form->submit($request->request->all());

        if ($form->isSubmitted() && $form->isValid()) {

            try {

                $post = $postService->create($post, $this->getUser());

            } catch (\Exception $e) {
                return $this->json($e->getMessage(), $e->getCode());

            }

            return $this->responseJson($post, ['groups' => ['postList', 'postUser', 'userList']]);
        } else {

            return $this->responseJson([
                'status' => self::RESPONSE_ERROR,
                'form' => $form
            ]);
        }
    }

    /**
     * Returns a list of posts
     *
     * @Route("/posts", methods={"GET"})
     *
     *
     * @param PostRepository $postRepository
     *
     * @return JsonResponse
     * @throws \Exception
     */
    public function listAction(PostRepository $postRepository): JsonResponse
    {

        try {
            $posts = $postRepository->findBy([], ['id' => 'DESC']);

        } catch (\Exception $e) {

            return $this->responseJsonError($e->getMessage(), $e->getCode());
        }

        return $this->responseJson($posts, ['groups' => ['postList', 'postUser', 'userList']]);
    }

    /**
     * Return the post
     *
     * @Route("/posts/{id}", methods={"GET"})
     *
     * @param Request $request
     * @param PostRepository $postRepository
     *
     * @return JsonResponse
     * @throws \Exception
     */
    public function readAction(Request $request, PostRepository $postRepository): JsonResponse
    {

        $post = $this->getPost($request, $postRepository);

        if (null === $post) {
            return $this->responseJsonError('The post does not exist.', 404);
        }

        return $this->responseJson($post, ['groups' => ['postDetails', 'postUser', 'userList', 'postComments', 'commentList', 'commentUser']]);
    }

    /**
     * Create a post comment
     *
     * @Route("/posts/{id}/comments", methods={"POST"})
     *
     *
     * @param Request $request
     * @param PostServiceInterface $postService
     * @param PostRepository $postRepository
     *
     * @return JsonResponse
     * @throws \Exception
     */
    public function createCommentAction(Request $request, PostRepository $postRepository, PostServiceInterface $postService): JsonResponse
    {

        $post = $this->getPost($request, $postRepository);

        if (null === $post) {
            return $this->responseJsonError('The post does not exist.', 404);
        }

        $comment = new Comment();
        $form = $this->createForm(CommentCreateType::class, $comment);

        $form->submit($request->request->all());

        if ($form->isSubmitted() && $form->isValid()) {

            try {

                $comment = $postService->addComment($post, $comment, $this->getUser());

            } catch (\Exception $e) {
                return $this->responseJsonError($e->getMessage(), $e->getCode());

            }

            return $this->responseJson($comment, ['groups' => ['commentList', 'commentUser', 'userList']]);
        } else {

            return $this->responseJson([
                'status' => self::RESPONSE_ERROR,
                'form' => $form
            ]);
        }
    }

    /**
     * Remove the post
     *
     * @Route("/posts/{id}", methods={"DELETE"})
     *
     *
     * @param Request $request
     * @param PostRepository $postRepository
     * @param PostServiceInterface $postService
     *
     * @return JsonResponse
     * @throws \Exception
     */
    public function removeAction(Request $request, PostRepository $postRepository, PostServiceInterface $postService): JsonResponse
    {

        $post = $this->getPost($request, $postRepository);

        if (null === $post) {
            return $this->responseJsonError('The post does not exist.', 404);
        }

        if ($post->getUser() !== $this->getUser()) {
            return $this->responseJsonError('The post does not exist.', 404);
        }

        try {
            $postService->remove($post);

        } catch (\Exception $e) {
            return $this->responseJsonError($e->getMessage(), $e->getCode());

        }

        return $this->responseJson(['deleted' => true]);
    }

}
