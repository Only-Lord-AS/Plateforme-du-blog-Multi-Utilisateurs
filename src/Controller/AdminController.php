<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Comment;
use App\Entity\User;
use App\Repository\ArticleRepository;
use App\Repository\CommentRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin', requirements: ['_locale' => 'en|fr|ar'])]
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{

    #[Route('/comments', name: 'admin_comments')]
    public function comments(CommentRepository $repo, \Symfony\Component\HttpFoundation\Request $request): Response
    {
        $filter = $request->query->get('filter', 'pending');
        $criteria = [];

        if ($filter === 'approved') {
            $criteria = ['isApproved' => true];
        } elseif ($filter === 'rejected') {
            $criteria = ['isApproved' => false];
        } else {
            // Pending is null
            $criteria = ['isApproved' => null];
        }

        return $this->render('admin/comments.html.twig', [
            'comments' => $repo->findBy($criteria, ['createdAt' => 'DESC']),
            'current_filter' => $filter
        ]);
    }

    #[Route('/comment/{id}/approve', name: 'admin_comment_approve')]
    public function approve(Comment $comment, EntityManagerInterface $em): Response
    {
        $comment->setIsApproved(true);
        $em->flush();

        return $this->redirectToRoute('admin_comments');
    }

    #[Route('/comment/{id}/reject', name: 'admin_comment_reject')]
    public function reject(Comment $comment, EntityManagerInterface $em): Response
    {
        $comment->setIsApproved(false);
        $em->flush();

        return $this->redirectToRoute('admin_comments');
    }

    #[Route('/comment/{id}/delete', name: 'admin_comment_delete')]
    public function delete(Comment $comment, EntityManagerInterface $em): Response
    {
        $em->remove($comment);
        $em->flush();

        return $this->redirectToRoute('admin_comments');
    }

    #[Route('/user/{id}/delete', name: 'admin_delete_user')]
    public function deleteUser(User $user, EntityManagerInterface $em): Response
    {
        $em->remove($user);
        $em->flush();

        $this->addFlash('success', 'User deleted with all associated articles, comments, and likes.');

        return $this->redirectToRoute('admin_dashboard');
    }

    #[Route('/article/{id}/delete', name: 'admin_delete_article')]
    public function deleteArticle(Article $article, EntityManagerInterface $em): Response
    {
        $em->remove($article);
        $em->flush();

        $this->addFlash('success', 'Article deleted.');

        return $this->redirectToRoute('admin_dashboard');
    }
}
