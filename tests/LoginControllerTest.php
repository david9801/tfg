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

        $this->assertPageContains($client, $container, 'profile', 'html:contains("Bienvenido,")');

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

        $this->assertPageContains($client, $container, 'index', 'h5:contains("¡Bienvenido al Servicio de Videollamadas lowcost para una plataforma de e-learning !")');
        $this->assertPageContains($client, $container, 'login', 'h1:contains("Por favor, entra aqui!")');
    }

    /**
     * @param \Symfony\Bundle\FrameworkBundle\KernelBrowser $client El cliente HTTP
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container El contenedor de servicios
     * @param string $routeName El nombre de la ruta para acceder a la página
     * @param string $selector El selector CSS para el elemento que se debe encontrar
     */
    private function assertPageContains(\Symfony\Bundle\FrameworkBundle\KernelBrowser $client, \Symfony\Component\DependencyInjection\ContainerInterface $container, string $routeName, string $selector): void
    {
        $crawler = $client->request('GET', $container->get('router')->generate($routeName));
        $this->assertGreaterThan(0, $crawler->filter($selector)->count());
    }

}

