<?php

namespace App\Controller;

use App\Entity\Lesson;
use App\Entity\Team;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/teams")
 */
class TeamsController extends AbstractController
{
    /**
     * @Route("/teams", name="teams")
     */
    public function index(): Response
    {
        return $this->render('teams/index.html.twig', [
            'controller_name' => 'TeamsController',
        ]);
    }

    /**
     * @Route("/{id}/members", name="team_members")
     */
    public function teamMembers(Team $team): Response
    {
        return $this->render('teams/members.html.twig', [
            'controller_name' => 'TeamsController',
            'team' => $team
        ]);
    }

    /**
     * @Route("/{id}/lessons", name="team_lessons")
     */
    public function teamLessons(Team $team): Response
    {
        return $this->render('teams/lessons.html.twig', [
            'controller_name' => 'TeamsController',
            'team' => $team
        ]);
    }

    /**
     * @Route("enterlesson/{id}/", name="enter_lesson")
     */
    public function enterLesson(Lesson $lesson): Response
    {
        return $this->render('teams/enterlesson.html.twig', [
            'controller_name' => 'TeamsController',
            'lesson' => $lesson
        ]);
    }
}
