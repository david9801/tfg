<?php

namespace App\Tests;

use Symfony\Component\Routing\Annotation\Route;

final class TeacherTeamControllerTest extends BaseTestCase
{

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

}

