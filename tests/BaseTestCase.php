<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BaseTestCase extends WebTestCase
{

    /**
     * @return \Symfony\Component\HttpKernel\KernelInterface
     */
    protected static function createKernel(array $options = [])
    {
        return new \App\Kernel('test', true);
    }

    /**
     * @return array
     */
    protected function loginTestClient(): array
    {
        // Creamos el cliente
        $client = static::createClient();
        $client->enableProfiler();
        $container = self::$container;
        $url = $container->get('router')->generate('login');

        // Accedemos a la página de inicio de sesión
        $crawler = $client->request('GET', $url);
        $this->assertResponseIsSuccessful();
        $this->formWriteTest($crawler, $client);
        return [$client, $container, $crawler];
    }

    /**
     * @param \Symfony\Component\DomCrawler\Crawler|null $crawler
     * @param \Symfony\Bundle\FrameworkBundle\KernelBrowser $client
     * @return void
     */
    protected function formWriteTest(?\Symfony\Component\DomCrawler\Crawler $crawler, \Symfony\Bundle\FrameworkBundle\KernelBrowser $client): void
    {
        // Iniciamos sesión como profesor con las credenciales de uno ya creado
        $form = $crawler->selectButton('Sign in')->form();
        $form['_username'] = 'uo257729@uniovi.es';
        $form['_password'] = '123456';
        $client->submit($form);
    }

}