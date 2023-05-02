<?php

namespace App\Controller;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
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
    public function turnOnLed(Request $request): Response
    {
        $process = new Process(['python3', realpath($this->getParameter('kernel.project_dir')) . '/src/Python/led.py', 'turn_on']);
        $process->run();

        $this->processNotSucessfull($process);

        return $this->render('raspberry/options.html.twig', [
            'controller_name' => 'Clases',
        ]);
    }

    /**
     * @Route("/index/actions/Off", name="raspberry_turnOFF")
     */
    public function turnOffLed(Request $request): Response
    {
        $process = new Process(['python3', realpath($this->getParameter('kernel.project_dir')) . '/src/Python/led.py', 'turn_off']);
        $process->run();

        $this->processNotSucessfull($process);

        return $this->render('raspberry/options.html.twig', [
            'controller_name' => 'Clases',
        ]);
    }

    /**
     * @param Process $process
     * @return void
     */
    private function processNotSucessfull(Process $process): void
    {
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }

}
