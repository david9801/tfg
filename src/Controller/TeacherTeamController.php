<?php

namespace App\Controller;

use App\Entity\Team;
use App\Form\TeamType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/team/teams")
 */
class TeacherTeamController extends AbstractController
{
    /**
     * @Route("", name="teams_admin")
     */
    public function index(): Response
    {
        $team = new Team();
        $form = $this->createForm(TeamType::class, $team, [
            'action' => $this->generateUrl('team_new'),
        ]);
        return $this->renderForm('teacher/teams/index.html.twig', [
            'controller_name' => 'Gestion de Equipos',
            'form' => $form,
        ]);
    }

    /**
     * @Route("/edit/{id}", name="team_edit")
     */
    public function edit(EntityManagerInterface $entityManager, Team $team, Request $request): Response
    {
        $form = $this->createForm(TeamType::class, $team);

        $form->handleRequest($request);
        if ($form->isSubmitted() and $form->isValid()){
            $entityManager->flush();
            $this->addFlash(
                'success',
                'Equipo editado con exito'
            );
            return $this->redirectToRoute('teams_admin');
        }

        return $this->renderForm('teacher/teams/edit.html.twig', [
            'controller_name' => 'Teacher',
            'form' => $form,
        ]);
    }

    /**
     * @Route("/new", name="team_new")
     */
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $team = new Team();
        $form = $this->createForm(TeamType::class, $team);
        $form->handleRequest($request);

        $this->newFormIsValid($form, $team, $entityManager);
        return $this->redirectToRoute('teams_admin');
    }

    /**
     * @param \Symfony\Component\Form\FormInterface $form
     * @param Team $team
     * @param EntityManagerInterface $entityManager
     * @return void
     */
    private function newFormIsValid(\Symfony\Component\Form\FormInterface $form, Team $team, EntityManagerInterface $entityManager): void
    {
        if ($form->isSubmitted() && $form->isValid()) {
            $team->setOwner($this->getUser());
            $entityManager->persist($team);
            $entityManager->flush();
            $this->addFlash(
                'success',
                'Equipo añadido con exito'
            );
        }
    }

    /**
     * @Route("/delete/{id}", name="team_delete")
     */
    public function delete(Team $team, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($team);
        $entityManager->flush();
        $this->addFlash('success', 'Equipo eliminado con exito');
        return $this->redirectToRoute('teams_admin');
    }

    /**
     * @Route("/students/{id}", name="team_students")
     */
    public function students(Team $team, Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $members = $this->getMembersOfTeam($team, $user);
        return $this->render('teams/membersWithOutOwner.html.twig', [
            'controller_name' => 'Alumnos',
            'team' => $team,
            'members' => $members,
        ]);
    }
    /**
     * @param Team $team
     * @param $user
     * @return \Doctrine\Common\Collections\Collection
     */
    private function getMembersOfTeam(Team $team, $user): \Doctrine\Common\Collections\Collection
    {
        $members = $team->getStudents()->filter(function ($student) use ($user) {
            return $student !== $user;
        });
        return $members;
    }

    /**
     * @Route("/students/delete/{id}/user/{userid}", name="team_students_delete")
     */
    public function deleteStudentTeam(Team $team, int $userid, UserRepository $userRepository, EntityManagerInterface $entityManager): Response
    {
        $team->removeStudent($userRepository->find($userid));
        $entityManager->flush();
        $this->addFlash(
            'success',
            'Alumno borrado del equipo'
        );
        return $this->redirectToRoute('team_students', ['id' => $team->getId()]);
    }

}
