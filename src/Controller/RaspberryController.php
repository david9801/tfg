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
        try {
            $process = Process::fromShellCommandline('sudo python /home/pi/Desktop/iot/aws-iot-device-sdk-python-v2/samples/pubsub.py turn_on');
            $process->run();
            $this->processNotSucessfull($process);
            return $this->redirectToRoute('raspberry_funcionalidades');
        } catch (\Exception $e) {
            return $this->returnError();
        }
    }

    /**
     * @Route("/index/actions/Off", name="raspberry_turnOFF")
     */
    public function turnOffLed(Request $request): Response
    {
        try {
            $process = Process::fromShellCommandline('sudo python /home/pi/Desktop/iot/aws-iot-device-sdk-python-v2/samples/pubsub.py turn_on');
            $process->run();
            $this->processNotSucessfull($process);
            return $this->redirectToRoute('raspberry_funcionalidades');
        } catch (\Exception $e) {
            return $this->returnErrorOff();
        }
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

    /**
     * @return Response
     */
    private function returnError(): Response
    {
        $errorMessage = "No se ha podido encender el LED. La conexión con la Raspberry Pi no está disponible en este momento.";
        return $this->render('raspberry/options.html.twig', [
            'error' => $errorMessage,
        ]);
    }

    /**
     * @return Response
     */
    private function returnErrorOff(): Response
    {
        $errorMessage = "No se ha podido apagar el LED. La conexión con la Raspberry Pi no está disponible en este momento.";
        return $this->render('raspberry/options.html.twig', [
            'error' => $errorMessage,
        ]);
    }
}
