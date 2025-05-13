<?php

// src/Controller/ProfileController.php

namespace App\Controller;

use App\Entity\User; // Replace with your User entity
use App\Form\ProfileFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class ProfileController extends AbstractController
{
    #[Route('/user/profile', name: 'app_profile')]
    public function index(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHashTool,
    ): Response {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('You must be logged in to access this page.');
        }

        $form = $this->createForm(ProfileFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $currentPassword = $form->get('currentPassword')->getData();

            // Verify current password
            if (!$passwordHashTool->isPasswordValid($user, $currentPassword)) {
                $this->addFlash('error', 'Your current password is incorrect.');

                return $this->redirectToRoute('app_profile');
            }

            // Update email
            $user->setEmail($form->get('email')->getData());

            // Update password if provided
            $newPassword = $form->get('newPassword')->getData();
            if ($newPassword) {
                $user->setPassword($passwordHashTool->hashPassword($user, $newPassword));
            }

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Your profile has been updated successfully!');

            return $this->redirectToRoute('app_profile');
        }

        return $this->render('profile/index.html.twig', [
            'profileForm' => $form->createView(),
        ]);
    }
}
