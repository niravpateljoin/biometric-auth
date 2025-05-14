<?php

declare(strict_types=1);

namespace App\Controller;

use App\Form\ProfileType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ProfileController extends AbstractController
{
    public function __construct(private readonly UserRepository $userRepository)
    {
    }

    #[Route('/profile', name: 'profile_index')]
    #[IsGranted('ROLE_USER')]
    public function index(): Response
    {
        $user = $this->getUser();
        return $this->render('profile/index.html.twig');
    }

    #[Route('/profile/edit', name: 'profile_edit')]
    #[IsGranted('ROLE_USER')]
    public function edit(Request $request): Response
    {
        $user = $this->getUser();
        $profileType = $this->createForm(ProfileType::class, $user);
        $profileType->handleRequest($request);

        if ($profileType->isSubmitted() && $profileType->isValid()) {
            try {
                $plainPassword = $profileType->get('password')->getData();
                $this->userRepository->saveUser($user, $plainPassword);
                $this->addFlash('success', 'profile successfully updated.');
                return $this->redirectToRoute('profile_index');
            } catch (\Throwable $e) {
                $this->addFlash('danger', $e->getMessage());
                return $this->redirectToRoute('profile_index');
            }
        }

        return $this->render('profile/edit.html.twig', [
            'profileType' => $profileType->createView(),
        ]);
    }
}
