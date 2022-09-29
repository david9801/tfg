<?php

namespace App\Controller;

use App\Entity\Lesson;
use App\Entity\Team;
use App\Form\LessonType;
use App\Repository\LessonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/teacher/lessons")
 */
class TeacherLessonController extends AbstractController
{
    /**
     * @Route("/", name="lessons_admin")
     */
    public function index(): Response
    {
        return $this->render('teacher/lessons/index.html.twig', [
            'controller_name' => 'TeacherLessonController',
        ]);
    }

    /**
     * @Route("/team/{id}", name="lessons_team_admin")
     */
    public function lessonsTeam(Team $team): Response
    {
        return $this->render('teacher/lessons/team.html.twig', [
            'controller_name' => 'TeacherLessonController',
            'team' => $team
        ]);
    }

    /**
     * @Route("/team/{id}/new", name="new_lesson")
     */
    public function newLesson(Team $team, Request $request, EntityManagerInterface $entityManager): Response
    {
        $lesson = new Lesson();
        $form = $this->createForm(LessonType::class, $lesson);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            $lesson->setJitsilesson(uniqid());
            $entityManager->persist($lesson);
            $team->addLesson($lesson);
            $entityManager->flush();
            $this->addFlash(
                'success',
                'Clase creada con exito'
            );
            return $this->redirectToRoute('lessons_team_admin', ['id' => $team->getId()]);
        }

        return $this->renderForm('teacher/lessons/new.html.twig', [
            'controller_name' => 'TeacherLessonController',
            'form' => $form,
            'team' => $team
        ]);
    }

    /**
     * @Route("/editlesson/{id}", name="edit_lesson")
     */
    public function editLesson(Lesson $lesson, Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(LessonType::class, $lesson);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){
            $entityManager->flush();
            $this->addFlash(
                'success',
                'Clase modificada con exito'
            );
            return $this->redirectToRoute('lessons_team', ['id' => $lesson->getTeam()->getId()]);
        }

        return $this->renderForm('teacher/lessons/edit.html.twig', [
            'controller_name' => 'TeacherLessonController',
            'form' => $form,
        ]);
    }

    /**
     * @Route("/deletelesson/{id}", name="delete_lesson")
     */
    public function deleteLesson(Lesson $lesson, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($lesson);
        $entityManager->flush();
        $this->addFlash(
            'success',
            'Clase eliminada'
        );
        return $this->redirectToRoute('lessons_team_admin', ['id' => $lesson->getTeam()->getId()]);
    }

    /**
     * @Route("/enterlesson/{id}", name="enter_lesson_admin")
     */
    public function enterLesson(Lesson $lesson): Response
    {
        return $this->renderForm('teacher/lessons/enterlesson.html.twig', [
            'controller_name' => 'TeacherLessonController',
            'lesson' => $lesson
        ]);
    }
}
