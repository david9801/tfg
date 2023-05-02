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
 * @Route("/raspberry")
 */
class RaspberryController extends AbstractController
{

    /**
     * @Route("/index", name="raspberry_controll")
     */
    public function index(): Response
    {
        return $this->render('raspberry/index.html.twig', [
            'controller_name' => 'Clases',
        ]);
    }

    /**
     * @Route("/index/actions", name="raspberry_funcionalidades")
     */
    public function showOptions(): Response
    {
        return $this->render('raspberry/options.html.twig', [
            'controller_name' => 'Clases',
        ]);
    }

    /**
     * @Route("/index/actions/On", name="raspberry_turnON")
     */
    public function turnOnLed(): Response
    {
        return $this->render('raspberry/options.html.twig', [
            'controller_name' => 'Clases',
        ]);
    }

    /**
     * @Route("/index/actions/Off", name="raspberry_turnOFF")
     */
    public function shutDownLed(): Response
    {
        return $this->render('raspberry/options.html.twig', [
            'controller_name' => 'Clases',
        ]);
    }

}
