<?php

namespace App\Tests;

use Symfony\Component\Routing\Annotation\Route;

class TeacherLessonControllerTest extends BaseTestCase
{

    /**
     * @Route("/login", name="login")
     * @Route("/", name="lessons_admin")
     * @Route("/team/{id}", name="lessons_team_admin")
     * @Route("/team/{id}/new", name="new_lesson")
     * @Route("/deletelesson/{id}", name="delete_lesson")
     */
    public function testNewLesson():void
    {
        list($client, $container, $crawler) = $this->loginTestClient();

        $crawler = $client->request('GET', $container->get('router')->generate('lessons_admin'));
        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $tbody = $crawler->filter('table.table tbody');
        if ($tbody->children()->count() === 0) {
            // El tbody está vacío, por lo tanto no hay equipos para listar
            $this->markTestSkipped('No hay equipos para listar');
            return;
        }

        $teamId = $crawler->filter('table.table tbody tr:first-child a.btn')->first()->attr('href');
        $teamId = substr($teamId, strrpos($teamId, '/') + 1);

        $url = $container->get('router')->generate('lessons_team_admin', ['id' => $teamId]);
        $client->request('GET', $url);
        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $newLessonUrl = $container->get('router')->generate('new_lesson', ['id' => $teamId]);
        $client->request('GET', $newLessonUrl);
        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $crawler = $client->getCrawler();
        $form = $crawler->selectButton('Guardar')->form();
        $form['lesson[title]'] = 'Lección de prueba';
        $client->submit($form);

        $this->assertSame(302, $client->getResponse()->getStatusCode());
        $this->assertResponseRedirects($container->get('router')->generate('lessons_team_admin', ['id' => $teamId]));

        $crawler = $client->followRedirect();
        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertGreaterThan(
            0,
            $crawler->filter('table.table tbody tr td:contains("Lección de prueba")')->count()
        );


    }


}

