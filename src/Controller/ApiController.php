<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api', name: 'api_')]
class ApiController extends AbstractController
{
    #[Route('/users/search', name: 'users_search', methods: ['GET'])]
    public function searchUsers(Request $request, UserRepository $userRepository): JsonResponse
    {
        $query = $request->query->get('q');

        if (!$query || strlen($query) < 1) {
            return $this->json([]);
        }

        // Search by nickname or email
        // We'll use a custom repository method or QueryBuilder for flexibility
        // For now, let's use a simple query builder here or assume a simplified search

        $qb = $userRepository->createQueryBuilder('u');
        $qb->where('u.nickname LIKE :query')
            ->orWhere('u.email LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->setMaxResults(5);

        $users = $qb->getQuery()->getResult();

        $data = [];
        foreach ($users as $user) {
            $data[] = [
                'id' => $user->getId(),
                'nickname' => $user->getNickname() ?? explode('@', $user->getEmail())[0],
                'email' => $user->getEmail(),
                'avatar' => $user->getAvatar(),
            ];
        }

        return $this->json($data);
    }
}
