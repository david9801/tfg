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
        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $form = $crawler->filter('form[name="team"]')->form();
        $this->assertCount(1, [$form]);

        $teamName = 'Equipo de prueba';
        $this->setFormValue($form, 'team[name]', $teamName);

        $crawler = $client->submit($form);

        $this->assertSame(302, $client->getResponse()->getStatusCode());

        $this->assertTrue($client->getResponse()->isRedirect('/team/teams'));

        $this->teamCreatedExists($client);
    }

    /**
     * @Route("/login", name="login")
     * @Route("", name="teams_admin")
     * @Route("/delete/{id}", name="team_delete")
     */
    public function testDeleteNewTeam(): void
    {
        list($client, $container, $crawler) = $this->loginTestClient();

        $teamName = 'Equipo de prueba';
        $teamToDelete = $this->getTeamByName($client,$teamName);
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
     * @param \Symfony\Component\DomCrawler\Form $form El formulario.
     * @param string $field El nombre del campo.
     * @param string $value El valor a establecer en el campo.
     */
    private function setFormValue(\Symfony\Component\DomCrawler\Form $form, string $field, string $value): void
    {
        $form[$field] = $value;
    }

    /**
     * @param \Symfony\Bundle\FrameworkBundle\KernelBrowser $client
     * @return void
     */
    private function teamCreatedExists(\Symfony\Bundle\FrameworkBundle\KernelBrowser $client): void
    {
        $crawler = $client->request('GET', '/team/teams');
        $this->assertGreaterThan(
            0,
            $crawler->filter('table.table tbody tr td:contains("Equipo de prueba")')->count()
        );
    }

}

