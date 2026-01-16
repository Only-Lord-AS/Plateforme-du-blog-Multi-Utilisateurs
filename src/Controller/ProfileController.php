<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ProfileType;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/profile')]
class ProfileController extends AbstractController
{
    #[Route('/edit', name: 'app_profile_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        /** @var \App\Entity\User|null $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $form = $this->createForm(ProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Handle Avatar Upload
            /** @var UploadedFile $avatarFile */
            $avatarFile = $form->get('avatarFile')->getData();
            if ($avatarFile) {
                $originalFilename = pathinfo($avatarFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $avatarFile->guessExtension();

                try {
                    $avatarFile->move(
                        $this->getParameter('kernel.project_dir') . '/public/uploads/avatars',
                        $newFilename
                    );
                    $user->setAvatar($newFilename);
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }
            }

            // Handle Banner Upload
            /** @var UploadedFile $bannerFile */
            $bannerFile = $form->get('bannerFile')->getData();
            if ($bannerFile) {
                $originalFilename = pathinfo($bannerFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $bannerFile->guessExtension();

                try {
                    $bannerFile->move(
                        $this->getParameter('kernel.project_dir') . '/public/uploads/banners',
                        $newFilename
                    );
                    $user->setBanner($newFilename);
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }
            }

            $entityManager->flush();

            return $this->redirectToRoute('app_profile_show', ['id' => $user->getId()]);
        }

        return $this->render('profile/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/edit/delete-avatar', name: 'app_profile_delete_avatar', methods: ['POST'])]
    public function deleteAvatar(EntityManagerInterface $entityManager): Response
    {
        /** @var \App\Entity\User|null $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        if ($user->getAvatar()) {
            $oldFile = $this->getParameter('kernel.project_dir') . '/public/uploads/avatars/' . $user->getAvatar();
            if (file_exists($oldFile)) {
                unlink($oldFile);
            }
            $user->setAvatar(null);
            $entityManager->flush();
            $this->addFlash('success', 'Profile picture removed.');
        }

        return $this->redirectToRoute('app_profile_edit');
    }

    #[Route('/edit/delete-banner', name: 'app_profile_delete_banner', methods: ['POST'])]
    public function deleteBanner(EntityManagerInterface $entityManager): Response
    {
        /** @var \App\Entity\User|null $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        if ($user->getBanner()) {
            $oldFile = $this->getParameter('kernel.project_dir') . '/public/uploads/banners/' . $user->getBanner();
            if (file_exists($oldFile)) {
                unlink($oldFile);
            }
            $user->setBanner(null);
            $entityManager->flush();
            $this->addFlash('success', 'Banner removed.');
        }

        return $this->redirectToRoute('app_profile_edit');
    }

    #[Route('/{id}/follow', name: 'app_profile_follow', methods: ['POST'])]
    public function follow(User $userToFollow, EntityManagerInterface $em): Response
    {
        /** @var \App\Entity\User $currentUser */
        $currentUser = $this->getUser();
        if (!$currentUser) {
            return $this->redirectToRoute('app_login');
        }

        if ($currentUser === $userToFollow) {
            return $this->redirectToRoute('app_profile_show', ['id' => $userToFollow->getId()]);
        }

        if (!$currentUser->isFollowing($userToFollow)) {
            $follow = new \App\Entity\Follow();
            $follow->setFollower($currentUser);
            $follow->setFollowing($userToFollow);
            $em->persist($follow);
            $em->flush();
        }

        return $this->redirectToRoute('app_profile_show', ['id' => $userToFollow->getId()]);
    }

    #[Route('/{id}/unfollow', name: 'app_profile_unfollow', methods: ['POST'])]
    public function unfollow(User $userToUnfollow, EntityManagerInterface $em): Response
    {
        /** @var \App\Entity\User $currentUser */
        $currentUser = $this->getUser();
        if (!$currentUser) {
            return $this->redirectToRoute('app_login');
        }

        foreach ($currentUser->getFollows() as $follow) {
            if ($follow->getFollowing() === $userToUnfollow) {
                $em->remove($follow);
                $em->flush();
                break;
            }
        }

        return $this->redirectToRoute('app_profile_show', ['id' => $userToUnfollow->getId()]);
    }

    #[Route('/{id}', name: 'app_profile_show', methods: ['GET'])]
    public function show(User $user, ArticleRepository $articleRepository): Response
    {
        // Calculate total likes received on user's articles
        $totalLikesReceived = 0;
        foreach ($user->getArticles() as $article) {
            foreach ($article->getLikes() as $like) {
                if ($like->isLike()) {
                    $totalLikesReceived++;
                }
            }
        }

        return $this->render('profile/show.html.twig', [
            'user' => $user,
            'articles' => $articleRepository->findBy(['author' => $user], ['createdAt' => 'DESC']),
            'totalLikesReceived' => $totalLikesReceived,
        ]);
    }
}
