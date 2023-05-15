<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Routing\Annotation\Route;

class LoginControllerTest extends WebTestCase
{

    /**
     * @return \Symfony\Component\HttpKernel\KernelInterface
     */
    protected static function createKernel(array $options = [])
    {
        return new \App\Kernel('test', true);
    }

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
     * @return array
     */
    private function loginTestClient(): array
    {
        // Creamos el cliente
        $client = static::createClient();
        $client->enableProfiler();
        $container = self::$container;
        $url = $container->get('router')->generate('login');

        //Accedemos a la pagina de inicio de sesion
        $crawler = $client->request('GET', $url);
        $this->assertResponseIsSuccessful();
        $this->formWriteTest($crawler, $client);
        return array($client, $container, $crawler);
    }



    /**
     * @param \Symfony\Component\DomCrawler\Crawler|null $crawler
     * @param \Symfony\Bundle\FrameworkBundle\KernelBrowser $client
     * @return void
     */
    private function formWriteTest(?\Symfony\Component\DomCrawler\Crawler $crawler, \Symfony\Bundle\FrameworkBundle\KernelBrowser $client): void
    {
        //iniciamos sesion como profesor con las credenciales de uno ya creado
        $form = $crawler->selectButton('Sign in')->form();
        $form['_username'] = 'uo257729@uniovi.es';
        $form['_password'] = '123456';
        $client->submit($form);
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

