<?php

namespace App\Controller;

use App\Repository\ArticleRepository;
use App\Repository\CommentRepository;
use App\Repository\UserRepository;
use App\Repository\LikeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractController
{
    #[Route('/admin/dashboard', name: 'admin_dashboard', requirements: ['_locale' => 'en|fr|ar'])]
    public function index(
        ArticleRepository $articleRepo,
        CommentRepository $commentRepo,
        UserRepository $userRepo
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->render('admin/dashboard.html.twig', [
            'articlesCount' => count($articleRepo->findAll()),
            'usersCount' => count($userRepo->findAll()),
            'approvedComments' => count($commentRepo->findBy(['isApproved' => true])),
            'pendingComments' => count($commentRepo->findBy(['isApproved' => null])),
            'rejectedComments' => count($commentRepo->findBy(['isApproved' => false])),
        ]);
    }

    #[Route('/admin/dashboard/data', name: 'admin_dashboard_data', requirements: ['_locale' => 'en|fr|ar'])]
    public function data(ArticleRepository $articleRepo, UserRepository $userRepo, Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $labels = [];
        $articles = [];
        $users = [];
        $locale = $request->getLocale();
        $now = new \DateTimeImmutable();

        // 1. Month Names Fallback for missing 'intl' extension
        $monthsMap = [
            'en' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            'fr' => ['janv.', 'févr.', 'mars', 'avr.', 'mai', 'juin', 'juil.', 'août', 'sept.', 'oct.', 'nov.', 'déc.'],
            'ar' => ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو', 'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر']
        ];

        // 2. Formatting Header Labels
        for ($i = 5; $i >= 0; $i--) {
            $date = $now->modify("first day of -{$i} month");
            $monthNum = (int) $date->format('n') - 1; // 0-11
            $year = $date->format('Y');

            if (class_exists('\IntlDateFormatter')) {
                $formatter = new \IntlDateFormatter($locale, \IntlDateFormatter::MEDIUM, \IntlDateFormatter::NONE, null, null, 'MMM yyyy');
                $labels[] = $formatter->format($date);
            } else {
                // Fallback to manual mapping
                $monthName = $monthsMap[$locale][$monthNum] ?? $monthsMap['en'][$monthNum];
                $labels[] = "{$monthName} {$year}";
            }

            // 3. Count Articles
            $start = $date->setTime(0, 0, 0);
            $end = $date->modify("last day of this month")->setTime(23, 59, 59);

            $articles[] = (int) $articleRepo->createQueryBuilder('a')
                ->select('COUNT(a.id)')
                ->andWhere('a.createdAt BETWEEN :start AND :end')
                ->setParameter('start', $start)
                ->setParameter('end', $end)
                ->getQuery()
                ->getSingleScalarResult();

            // 4. Count Users (Cumulative)
            $users[] = (int) $userRepo->createQueryBuilder('u')
                ->select('COUNT(u.id)')
                ->andWhere('u.createdAt <= :end')
                ->setParameter('end', $end)
                ->getQuery()
                ->getSingleScalarResult();
        }

        return new JsonResponse([
            'labels' => $labels,
            'articles' => $articles,
            'users' => $users,
        ]);
    }

    #[Route('/admin/users', name: 'admin_users', requirements: ['_locale' => 'en|fr|ar'])]
    public function users(
        UserRepository $userRepo,
        ArticleRepository $articleRepo,
        CommentRepository $commentRepo,
        LikeRepository $likeRepo
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $users = $userRepo->findAll();
        $usersData = [];

        foreach ($users as $user) {
            $articles = $articleRepo->findBy(['author' => $user]);

            // Group articles by category
            $categories = [];
            foreach ($articles as $article) {
                $catName = $article->getCategory()->getName();
                if (!isset($categories[$catName])) {
                    $categories[$catName] = 0;
                }
                $categories[$catName]++;
            }

            // Count likes and dislikes on user's articles
            $totalLikes = 0;
            $totalDislikes = 0;
            foreach ($articles as $article) {
                foreach ($article->getLikes() as $like) {
                    if ($like->isLike()) {
                        $totalLikes++;
                    } else {
                        $totalDislikes++;
                    }
                }
            }

            // Count user's comments
            $userComments = $commentRepo->findBy(['author' => $user]);

            $usersData[] = [
                'user' => $user,
                'articlesCount' => count($articles),
                'categories' => $categories,
                'likes' => $totalLikes,
                'dislikes' => $totalDislikes,
                'commentsCount' => count($userComments),
            ];
        }

        return $this->render('admin/users.html.twig', [
            'usersData' => $usersData,
        ]);
    }

    #[Route('/admin/user/{id}/delete', name: 'admin_user_delete', methods: ['POST'], requirements: ['_locale' => 'en|fr|ar'])]
    public function deleteUser(
        Request $request,
        \App\Entity\User $user,
        EntityManagerInterface $em
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // Prevent admin from deleting themselves
        if ($user === $this->getUser()) {
            $this->addFlash('error', 'You cannot delete your own account.');
            return $this->redirectToRoute('admin_users', ['_locale' => $request->getLocale()]);
        }

        $token = $request->request->get('_token');
        if ($this->isCsrfTokenValid('delete_user' . $user->getId(), $token)) {
            $em->remove($user);
            $em->flush();
            $this->addFlash('success', 'User deleted successfully with all their content.');
        } else {
            $this->addFlash('error', 'Invalid CSRF token.');
        }

        return $this->redirectToRoute('admin_users', ['_locale' => $request->getLocale()]);
    }
}

