<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Routing\Annotation\Route;
use PHPUnit\Framework\TestCase;

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
        // Creamos el cliente
        $client = static::createClient();
        $client->enableProfiler();
        $container = self::$container;
        $url = $container->get('router')->generate('login');

        //Accedemos a la pagina de inicio de sesion
        $crawler = $client->request('GET', $url);
        $this->assertResponseIsSuccessful();

        //iniciamos sesion como profesor con las credenciales de uno ya creado
        $form = $crawler->selectButton('Sign in')->form();
        $form['_username'] = 'uo257729@uniovi.es';
        $form['_password'] = '123456';
        $client->submit($form);

        $crawler = $client->request('GET', $container->get('router')->generate('profile'));
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Bienvenido,")')->count()
        );
    }

}





// Verificamos que se haya iniciado sesión correctamente
//$this->assertEquals(500, $client->getResponse()->getStatusCode());

//Comprobamos que estamos logueados

