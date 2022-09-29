<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\EditStudentFormType;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/teacher/students")
 */
class TeacherStudentController extends AbstractController
{
    /**
     * @Route("/", name="students_admin")
     */
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('teacher/students/index.html.twig', [
            'users' => $userRepository->findAll(),
            'controller_name' => 'Gestion Alumnos',
        ]);
    }

    /**
     * @Route("/edit/{id}", name="student_edit")
     */
    public function edit(EntityManagerInterface $entityManager, UserPasswordHasherInterface $userPasswordHasher, User $user, Request $request): Response
    {
        $form = $this->createForm(EditStudentFormType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() and $form->isValid()){
            if (!is_null($form->get('plainPassword')->getData())){
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
                'Usuario editado con exito'
            );
            return $this->redirectToRoute('students_admin');

        }

        return $this->renderForm('teacher/students/edit.html.twig', [
            'controller_name' => 'Teacher',
            'form' => $form,
        ]);
    }

    /**
     * @Route("/new", name="student_new")
     */
    public function new(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager->persist($user);
            $entityManager->flush();
            $this->addFlash(
                'success',
                'Usuario creado con exito'
            );
            return $this->redirectToRoute('students_admin');

        }

        return $this->renderForm('teacher/students/new.html.twig', [
            'controller_name' => 'Teacher',
            'form' => $form,
        ]);
    }

    /**
     * @Route("/delete/{id}", name="student_delete")
     */
    public function delete(User $user, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($user);
        $entityManager->flush();
        $this->addFlash('success', 'Usuario eliminado con exito');
        return $this->redirectToRoute('students_admin');
    }
}
