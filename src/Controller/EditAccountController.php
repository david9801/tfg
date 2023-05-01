<?php

namespace App\Controller;

use App\Form\EditStudentFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class EditAccountController extends AbstractController
{
    /**
     * @Route("/edit/account", name="edit_account")
     */
    public function index(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(EditStudentFormType::class, $user);
        $form->handleRequest($request);

        $this->checkedFormSubmitted($form, $user, $userPasswordHasher, $entityManager);

        return $this->renderForm('edit_account/index.html.twig', [
            'controller_name' => 'EditAccountController',
            'form' => $form,
        ]);
    }

    /**
     * @param \Symfony\Component\Form\FormInterface $form
     * @param \Symfony\Component\Security\Core\User\UserInterface|null $user
     * @param UserPasswordHasherInterface $userPasswordHasher
     * @param EntityManagerInterface $entityManager
     * @return void
     */
    private function checkedFormSubmitted(\Symfony\Component\Form\FormInterface $form, ?\Symfony\Component\Security\Core\User\UserInterface $user, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): void
    {
        if ($form->isSubmitted() && $form->isValid()) {
            if (!is_null($form->get('plainPassword')->getData())) {
                //if password is set, update
                // encode the plain password
                $user->setPassword(
                    $userPasswordHasher->hashPassword(
                        $user,
                        $form->get('plainPassword')->getData()
                    )
                );
            }
            $entityManager->flush();

            $this->addFlash(
                'success',
                'Cuenta modificada con exito'
            );
        }
    }
}
