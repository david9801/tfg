<?php

namespace App\Tests;

use Symfony\Component\Routing\Annotation\Route;

final class LoginControllerTest extends BaseTestCase
{

    /**
     * @Route("/login", name="login")
     * @Route("/profile", name="profile")
     */
    public function testIsLoggedIn()
    {
        list($client, $container, $crawler) = $this->loginTestClient();
        $crawler = $client->request('GET', $container->get('router')->generate('profile'));
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Bienvenido,")')->count()
        );
    }
    /**
     * @Route("/", name="index")
     * @Route("/login", name="login")
     * @Route("/profile", name="profile")
     * @Route("/logout", name="logout", methods={"GET"})
     */
    public function testIsLoggedOut()
    {
        list($client, $container, $crawler) = $this->loginTestClient();
        $crawler = $client->request('GET', $container->get('router')->generate('profile'));
        $crawler = $client->request('GET', $container->get('router')->generate('logout'));

        // Verificamos que hemos sido deslogueados y que se nos muestra el mensaje de bienvenida al inicio de sesión
        $crawler = $client->request('GET', $container->get('router')->generate('index'));

        $this->assertGreaterThan(
            0,
            $crawler->filter('h5:contains("¡Bienvenido al Servicio de Videollamadas lowcost para una plataforma de e-learning !")')->count()
        );
        $crawler = $client->request('GET', $container->get('router')->generate('login'));

        $this->assertGreaterThan(
            0,
            $crawler->filter('h1:contains("Por favor, entra aqui!")')->count()
        );

    }

}

