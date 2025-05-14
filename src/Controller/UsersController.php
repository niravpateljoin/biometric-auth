<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/users')]
class UsersController extends AbstractController
{
    public function __construct(private readonly UserRepository $userRepository)
    {
    }

    #[Route('/', name: 'users_index')]
    #[IsGranted('ROLE_ADMIN')]
    public function index(Request $request, PaginatorInterface $paginator): Response
    {
        $numberOfRecords = $request->query->getInt('numberOfRecords', 25);
        $users = $paginator->paginate($this->userRepository->createQueryBuilder('u')
            ->getQuery(), $request->query->getInt('page', 1), $numberOfRecords);

        return $this->render('users/index.html.twig', [
            'users' => $users,
            'numberOfRecords' => $numberOfRecords,
        ]);
    }

    #[Route('/new', name: 'users_new')]
    #[IsGranted('ROLE_ADMIN')]
    public function new(Request $request): Response
    {
        $user = new User();
        $userTypeForm = $this->createForm(UserType::class, $user, [
            'is_edit_action' => false
        ]);
        $userTypeForm->handleRequest($request);

        if ($userTypeForm->isSubmitted() && $userTypeForm->isValid()) {
            try {
                $plainPassword = $userTypeForm->get('password')->getData();
                $this->userRepository->saveUser($user, $plainPassword);
                $this->addFlash('success', 'User successfully created.');
                return $this->redirectToRoute('users_index');
            } catch (\Throwable $e) {
                $this->addFlash('danger', $e->getMessage());
                return $this->redirectToRoute('users_index');
            }
        }

        return $this->render('users/new.html.twig', [
            'userTypeForm' => $userTypeForm->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'users_edit')]
    #[IsGranted('ROLE_SUPER_ADMIN')]
    public function edit(Request $request, User $user): Response
    {
        $userTypeForm = $this->createForm(UserType::class, $user, [
            'is_edit_action' => true
        ]);
        $userTypeForm->handleRequest($request);

        if ($userTypeForm->isSubmitted() && $userTypeForm->isValid()) {
            try {
                $plainPassword = $userTypeForm->get('password')->getData();
                $this->userRepository->saveUser($user, $plainPassword);
                $this->addFlash('success', 'User successfully updated.');
                return $this->redirectToRoute('users_index');
            } catch (\Throwable $e) {
                $this->addFlash('danger', $e->getMessage());
                return $this->redirectToRoute('users_index');
            }
        }

        return $this->render('users/edit.html.twig', [
            'user' => $user,
            'userTypeForm' => $userTypeForm->createView(),
        ]);
    }

    #[Route('/{id}/delete', name: 'users_delete')]
    #[IsGranted('ROLE_SUPER_ADMIN')]
    public function delete(Request $request, User $user): JsonResponse
    {
        $csrfToken = $request->request->get('_token');
        $status = false;
        $errorMessage = null;
        
        if (!$this->isCsrfTokenValid('delete_user', $csrfToken)) {
            $errorMessage = 'Invalid CSRF token';
        } else {
            try {
                $this->userRepository->remove($user);
                $status = true;
            } catch (\Throwable $e) {
                $errorMessage = 'Failed to delete user, ' . $e->getMessage();
            }
        }

        return $this->json([
            'status' => $status,
            'errorMessage' => $errorMessage
        ]);
    }
}
