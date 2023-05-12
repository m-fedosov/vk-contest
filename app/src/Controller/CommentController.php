<?php

namespace App\Controller;

use App\Entity\Comment;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class CommentController extends AbstractController
{
    #[Route("/comment", methods: 'POST')]
    public function createComment(Request $request, EntityManagerInterface $entityManager, #[CurrentUser] ?User $user): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Получаем текст комментария
        $text = $data['text'] ?? '';
        if (empty($text)) {
            return new JsonResponse(['error' => 'Text is required.'], Response::HTTP_BAD_REQUEST);
        }

        // Получаем идентификатор родительского комментария, если есть
        $parentId = $data['parent_id'] ?? null;
        if ($parentId !== null){
            $parentComment = $entityManager->getRepository(Comment::class)->find($parentId);
        } else {
            $parentComment = null;
        }

        // Получаем идентификатор пользователя
        $userId = $user->getId() ?? null;
        if ($userId === null) {
            return new JsonResponse(['error' => 'User is required.'], Response::HTTP_BAD_REQUEST);
        }

        // Создаем новый комментарийelse if ($commentUserId === null)
        $comment = new Comment();
        $comment->setCreatedAt(new DateTimeImmutable("now"));

        $comment->setText($text);
        $comment->setParent($parentComment);

        $comment->setUserId($userId);

        $entityManager->persist($comment);
        $entityManager->flush();

        return new JsonResponse(['id' => $comment->getId()], Response::HTTP_CREATED);
    }

    #[Route("/comment/{id}", methods: 'PUT')]
    public function updateComment(Request $request, int $id, EntityManagerInterface $entityManager, #[CurrentUser] ?User $user): JsonResponse
    {
        $comment = $entityManager->getRepository(Comment::class)->find($id);

        if (!$comment) {
            throw $this->createNotFoundException(
                'No comment found for id '.$id
            );
        }

        $data = json_decode($request->getContent(), true);

        $userId = $user->getId();
        $commentUserId = $comment->getUserId();

        if ($commentUserId !== $userId) {
            return new JsonResponse(['error' => "You cannot edit someone else's comment"], Response::HTTP_BAD_REQUEST);
        }

        $comment->setText($data['text']);

        $entityManager->flush();

        return new JsonResponse([
            'id' => $comment->getId(),
            'text' => $comment->getText(),
            'user_id' => $comment->getUserId()
        ]);
    }

    #[Route("/comment/{id}", methods: ['GET'])]
    public function getComment(int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        $comment = $entityManager->getRepository(Comment::class)->find($id);
        if (!$comment) {
            throw $this->createNotFoundException(
                'No comment found for id '.$id
            );
        }

        $commentCreatedAt = $comment->getCreatedAt();
        if ($commentCreatedAt !== null) {
            $commentCreatedAt = $commentCreatedAt->format('Y-m-d H:i:s');
        }

        $responseData = [
            'id' => $comment->getId(),
            'text' => $comment->getText(),
            'created_at' => $commentCreatedAt,
            'user_id' => $comment->getUserId(),
        ];

        $responseData['parent_comment'] = $this->buildCommentTree($comment->getParent());

        return new JsonResponse($responseData);
    }

    private function buildCommentTree(?Comment $comment): ?array
    {
        if ($comment === null) {
            return null;
        }

        $commentCreatedAt = $comment->getCreatedAt();
        if ($commentCreatedAt !== null) {
            $commentCreatedAt = $commentCreatedAt->format('Y-m-d H:i:s');
        }

        $responseData = [
            'id' => $comment->getId(),
            'text' => $comment->getText(),
            'created_at' => $commentCreatedAt,
            'user_id' => $comment->getUserId()
        ];

        $responseData['parent_comment'] = $this->buildCommentTree($comment->getParent());

        return $responseData;
    }

    #[Route("/comment/{id}", methods: 'DELETE')]
    public function deleteComment(int $id, EntityManagerInterface $entityManager, #[CurrentUser] ?User $user): JsonResponse
    {
        $comment = $entityManager->getRepository(Comment::class)->find($id);

        if (!$comment) {
            throw $this->createNotFoundException(
                'No comment found for id '.$id
            );
        }

        $userId = $user->getId();
        $commentUserId = $comment->getUserId();

        if ($commentUserId !== $userId) {
            return new JsonResponse(['error' => "You cannot delete someone else's comment"], Response::HTTP_BAD_REQUEST);
        }

        $entityManager->remove($comment);
        $entityManager->flush();

        return new JsonResponse("Comment with id {$id} was successfully deleted.");
    }
}
