<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Routing\Annotation\Route;

class TeacherTeamControllerTest extends WebTestCase
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
     * @Route("", name="teams_admin")
     * @Route("/new", name="team_new")
     */
    public function testNewTeam():void
    {
        list($client, $container, $crawler) = $this->loginTestClient();

        $crawler = $client->request('GET', $container->get('router')->generate('teams_admin'));
        // vista funciona
        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $form = $crawler->filter('form[name="team"]')->form();
        $this->assertCount(1, [$form]);

        $form['team[name]'] = 'Equipo de prueba';

        $crawler = $client->submit($form);

        // Verificar que la respuesta HTTP es 302 (redirección)
        $this->assertSame(302, $client->getResponse()->getStatusCode());

        // Verificar que se redirecciona a la página de administración de equipos
        $this->assertTrue($client->getResponse()->isRedirect('/team/teams'));

        // Visitar la página de administración de equipos
        $crawler = $client->request('GET', '/team/teams');

        // Verificar que el equipo creado aparece en la lista
        $this->assertGreaterThan(
            0,
            $crawler->filter('table.table tbody tr td:contains("Equipo de prueba")')->count()
        );
    }

    /**
     * @Route("/login", name="login")
     * @Route("", name="teams_admin")
     * @Route("/delete/{id}", name="team_delete")
     */
    public function testDeleteNewTeam(): void
    {
        list($client, $container, $crawler) = $this->loginTestClient();

        $teamToDelete = $this->getTeamByName($client, 'Equipo de prueba');
        $this->assertNotNull($teamToDelete);

        $deleteUrl = $container->get('router')->generate('team_delete', ['id' => $teamToDelete->getId()]);
        $crawler = $client->request('GET', $deleteUrl);
        $this->assertTrue($client->getResponse()->isRedirect('/team/teams'));

        $crawler = $client->request('GET', $container->get('router')->generate('teams_admin'));

        $this->assertCount(0, $crawler->filter('table.table tbody tr td:contains("Equipo de prueba")'));
    }

    /**
     * Obtener el equipo por su nombre
     * @param \Symfony\Bundle\FrameworkBundle\KernelBrowser $client
     * @param string $teamName
     * @return \App\Entity\Team|null
     */
    private function getTeamByName(\Symfony\Bundle\FrameworkBundle\KernelBrowser $client, string $teamName): ?\App\Entity\Team
    {
        $em = $client->getContainer()->get('doctrine')->getManager();
        $repository = $em->getRepository(\App\Entity\Team::class);
        return $repository->findOneBy(['name' => $teamName]);
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

}

