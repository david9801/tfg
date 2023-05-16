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
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
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

    /**
     * @Route("/upload/{id}", name="team_upload_notes")
     */
    public function uploadFilesView($id): Response
    {
        return $this->renderForm('notes/notes.html.twig', [
            'id' => $id
        ]);

    }

    /**
     * @Route("/upload/{id}/file", name="upload_file")
     */
    public function uploadFiles(Request $request, $id): Response
    {
        if ($request->isMethod('POST')) {
            $file = $request->files->get('file');

            if ($file instanceof UploadedFile) {
                $allowedExtensions = ['ppt', 'pptx', 'pdf', 'xlsx', 'xls', 'docx'];
                $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $extension = $file->guessExtension();

                if (in_array($extension, $allowedExtensions)) {
                    $uploadsDirectory = $this->getParameter('kernel.project_dir') . '/public/uploads/' . $id;
                    if (!is_dir($uploadsDirectory)) {
                        mkdir($uploadsDirectory, 0777, true);
                    }

                    $newFilename = uniqid() . '.' . $extension;

                    $file->move($uploadsDirectory, $newFilename);

                    $this->addFlash('success', 'Archivo subido exitosamente.');
                } else {
                    $this->addFlash('error', 'Archivo NO permitido');
                }
            }
        }

        return $this->render('notes/notes.html.twig', [
            'id' => $id,
        ]);
    }

    /**
     * @Route("/showfiles/{id}", name="team_file")
     */
    public function showFiles($id): Response
    {

        $directory = $this->getParameter('kernel.project_dir') . '/public/uploads/' . $id;

        $files = [];
        if (is_dir($directory)) {
            $files = scandir($directory);
            $files = array_filter($files, function($file) use ($directory) {
                return is_file($directory . '/' . $file);
            });

        }

        return $this->render('notes/index.html.twig', [
            'files' => $files,
            'id' => $id,
        ]);
    }

    /**
     * @Route("/download/{id}/{filename}", name="download_file")
     */
    public function downloadFile($filename, $id):Response
    {
        $filePath = $this->getParameter('kernel.project_dir') . '/public/uploads/' . $id . '/' . $filename;

        if (!file_exists($filePath)) {
            throw $this->createNotFoundException('El archivo no existe.');
        }

        $response = new BinaryFileResponse($filePath);

        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $filename);

        $response->headers->set('Content-Type', 'application/octet-stream');

        return $response;
    }

}
