<?php

namespace App\Controller;

use App\Entity\User;
use App\Security\UserProvider;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;

/**
 * JWT Authentication controller for App4 admin interface
 */
#[Route('/auth')]
class AdminAuthController extends AbstractController
{
    public function __construct(
        private readonly UserProvider $userProvider,
        private readonly JWTTokenManagerInterface $jwtManager
    ) {
    }

    /**
     * Login endpoint - authenticates user and returns JWT token
     */
    #[Route('/login', name: 'api_auth_login', methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $username = $data['username'] ?? '';
        $password = $data['password'] ?? '';

        if (empty($username) || empty($password)) {
            return $this->json([
                'message' => 'Username and password are required'
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            /** @var User $user */
            $user = $this->userProvider->loadUserByIdentifier($username);
        } catch (UserNotFoundException) {
            return $this->json([
                'message' => 'Invalid credentials'
            ], Response::HTTP_UNAUTHORIZED);
        }

        // Legacy password check using MD5 (as used in existing PHP code)
        // Note: This is for backward compatibility with existing user passwords
        if ($user->getPassword() !== md5($password)) {
            return $this->json([
                'message' => 'Invalid credentials'
            ], Response::HTTP_UNAUTHORIZED);
        }

        // Generate JWT token
        $token = $this->jwtManager->create($user);

        return $this->json([
            'token' => $token,
            'user' => $user->toArray()
        ]);
    }

    /**
     * Get current authenticated user info
     */
    #[Route('/me', name: 'api_auth_me', methods: ['GET'])]
    public function me(): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();

        if (!$user) {
            return $this->json([
                'message' => 'Not authenticated'
            ], Response::HTTP_UNAUTHORIZED);
        }

        return $this->json([
            'user' => $user->toArray()
        ]);
    }

    /**
     * Refresh token endpoint
     */
    #[Route('/refresh', name: 'api_auth_refresh', methods: ['POST'])]
    public function refresh(): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->getUser();

        if (!$user) {
            return $this->json([
                'message' => 'Not authenticated'
            ], Response::HTTP_UNAUTHORIZED);
        }

        // Generate new JWT token
        $token = $this->jwtManager->create($user);

        return $this->json([
            'token' => $token,
            'user' => $user->toArray()
        ]);
    }
}
