<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Like;
use App\Repository\LikeRepository;
use App\Form\ArticleType;
use App\Repository\ArticleRepository;
use App\Service\SensitiveWordFilter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

#[Route('/article', requirements: ['_locale' => 'en|fr|ar'])]
final class ArticleController extends AbstractController
{
    #[Route(name: 'app_article_index', methods: ['GET'])]
    public function index(ArticleRepository $articleRepository): Response
    {
        return $this->render('article/index.html.twig', [
            'articles' => $articleRepository->findBy([], ['createdAt' => 'DESC']),
        ]);
    }

    #[Route('/new', name: 'app_article_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SensitiveWordFilter $filter, SluggerInterface $slugger): Response
    {
        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$filter->isSafe($article->getTitle() . ' ' . $article->getContent())) {
                $this->addFlash('error', 'Article contains prohibited content.');
                return $this->render('article/new.html.twig', [
                    'article' => $article,
                    'form' => $form,
                ]);
            }

            $article->setAuthor($this->getUser());

            // Handle Media Upload
            /** @var \Symfony\Component\HttpFoundation\File\UploadedFile|null $mediaFile */
            $mediaFile = $article->getMediaUpload();

            if ($mediaFile) {
                // Determine Media Type
                $imageExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
                $guessExtension = strtolower($mediaFile->guessExtension() ?? pathinfo($mediaFile->getClientOriginalName(), PATHINFO_EXTENSION));

                if (in_array($guessExtension, $imageExtensions)) {
                    $article->setMediaType('image');
                } else {
                    $article->setMediaType('document');
                }

                $originalFilename = pathinfo($mediaFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $guessExtension;

                try {
                    $mediaFile->move(
                        $this->getParameter('kernel.project_dir') . '/public/uploads/media',
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('error', 'Failed to upload media.');
                }

                $article->setMediaFilename($newFilename);
            }

            $entityManager->persist($article);
            $entityManager->flush();

            return $this->redirectToRoute('app_home', ['_locale' => $request->attributes->get('_locale') ?? 'en'], Response::HTTP_SEE_OTHER);
        }

        return $this->render('article/new.html.twig', [
            'article' => $article,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_article_show', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function show(
        Article $article,
        Request $request,
        EntityManagerInterface $em,
        SensitiveWordFilter $wordFilter
    ): Response {
        $comment = new \App\Entity\Comment();

        $form = $this->createForm(\App\Form\CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && $this->getUser()) {
            $comment->setArticle($article);
            $comment->setAuthor($this->getUser());

            // Auto-Moderation Logic
            // If content is safe -> Approve immediately
            // If content contains forbidden words -> Reject (set approved = false)
            if ($wordFilter->isSafe($comment->getContent())) {
                $comment->setIsApproved(true);
                $this->addFlash('success', 'flash.comment_submitted_auto');
            } else {
                $comment->setIsApproved(false);
                $this->addFlash('error', 'Comment contains prohibited words and was rejected.');
            }

            $em->persist($comment);
            $em->flush();

            return $this->redirectToRoute('app_article_show', [
                'id' => $article->getId(),
                '_locale' => $request->attributes->get('_locale') ?? 'en',
            ]);
        }

        return $this->render('article/show.html.twig', [
            'article' => $article,
            'commentForm' => $form->createView(),
        ]);
    }

    #[Route('/{id}/like/{type}', name: 'article_like', requirements: ['id' => '\d+'])]
    public function like(
        Request $request,
        Article $article,
        string $type,
        LikeRepository $repo,
        EntityManagerInterface $em
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();

        $existing = $repo->findOneBy([
            'article' => $article,
            'user' => $user,
        ]);

        if ($existing) {
            $existing->setIsLike($type === 'like');
        } else {
            $like = new Like();
            $like->setArticle($article);
            $like->setUser($user);
            $like->setIsLike($type === 'like');

            $em->persist($like);
        }

        $em->flush();

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse([
                'count' => $repo->count(['article' => $article, 'isLike' => $type === 'like']),
                'totalLikes' => $repo->count(['article' => $article, 'isLike' => true]),
                'totalDislikes' => $repo->count(['article' => $article, 'isLike' => false]),
                'isActive' => $type === 'like' // Simple feedback for JS
            ]);
        }

        return $this->redirectToRoute('app_article_show', [
            'id' => $article->getId(),
            '_locale' => $request->attributes->get('_locale') ?? 'en',
        ]);
    }

    #[Route('/{id}/edit', name: 'app_article_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Article $article, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Handle Media Upload
            /** @var \Symfony\Component\HttpFoundation\File\UploadedFile $mediaFile */
            $mediaFile = $form->get('mediaUpload')->getData();

            if ($mediaFile) {
                // Determine Media Type
                $imageExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
                $guessExtension = strtolower($mediaFile->guessExtension() ?? pathinfo($mediaFile->getClientOriginalName(), PATHINFO_EXTENSION));

                if (in_array($guessExtension, $imageExtensions)) {
                    $article->setMediaType('image');
                } else {
                    $article->setMediaType('document');
                }

                $originalFilename = pathinfo($mediaFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $guessExtension;

                try {
                    $mediaFile->move(
                        $this->getParameter('kernel.project_dir') . '/public/uploads/media',
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('error', 'Failed to upload media.');
                }

                $article->setMediaFilename($newFilename);
            }

            $entityManager->flush();

            return $this->redirectToRoute('app_article_index', ['_locale' => $request->attributes->get('_locale') ?? 'en'], Response::HTTP_SEE_OTHER);
        }

        return $this->render('article/edit.html.twig', [
            'article' => $article,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_article_delete', methods: ['POST'])]
    public function delete(Request $request, Article $article, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        // Ensure user is logged in and is our User entity
        if (!$user instanceof \App\Entity\User) {
            throw $this->createAccessDeniedException('You must be logged in to perform this action.');
        }

        // Only allow the author or admins to delete articles
        if ($user->getId() !== $article->getAuthor()->getId() && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('You do not have permission to delete this article.');
        }

        $token = $request->request->get('_token');
        if ($this->isCsrfTokenValid('delete' . $article->getId(), $token)) {
            $entityManager->remove($article);
            $entityManager->flush();
            $this->addFlash('success', 'flash.article_deleted');
        } else {
            $this->addFlash('error', 'msg.error');
        }

        return $this->redirectToRoute('app_article_index', ['_locale' => $request->attributes->get('_locale') ?? 'en'], Response::HTTP_SEE_OTHER);
    }
}
