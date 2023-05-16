<?php

namespace App\Tests;

use Symfony\Component\Routing\Annotation\Route;

final class TeacherLessonControllerTest extends BaseTestCase
{
    /**
     * @Route("/login", name="login")
     * @Route("/", name="lessons_admin")
     * @Route("/team/{id}", name="lessons_team_admin")
     * @Route("/team/{id}/new", name="new_lesson")
     */
    public function testNewLesson(): void
    {
        list($client, $container, $crawler) = $this->loginTestClient();

        $crawler = $client->request('GET', $container->get('router')->generate('lessons_admin'));
        $this->assertSame(200, $client->getResponse()->getStatusCode());

        if (!$this->hasTeamsToTest($crawler)) {
            $this->markTestSkipped('No hay equipos para listar');
            return;
        }

        $teamId = $this->getFirstTeamId($crawler);
        $lessonsAdminUrl = $container->get('router')->generate('lessons_team_admin', ['id' => $teamId]);
        $this->accessLessonsAdminPage($client, $lessonsAdminUrl);

        $newLessonUrl = $container->get('router')->generate('new_lesson', ['id' => $teamId]);
        $this->accessNewLessonPage($client, $newLessonUrl);

        $this->createNewLesson($client, 'Lección de prueba');

        $this->assertResponseRedirects($container->get('router')->generate('lessons_team_admin', ['id' => $teamId]));

        $crawler = $client->followRedirect();
        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertLessonExists($crawler, 'Lección de prueba');
    }

    /**
     * @Route("/login", name="login")
     * @Route("/", name="lessons_admin")
     * @Route("/team/{id}", name="lessons_team_admin")
     * @Route("/deletelesson/{id}", name="delete_lesson")
     */
    public function testDeleteNewLesson(): void
    {
        list($client, $container, $crawler) = $this->loginTestClient();

        $crawler = $client->request('GET', $container->get('router')->generate('lessons_admin'));
        $this->assertSame(200, $client->getResponse()->getStatusCode());

        if (!$this->hasTeamsToTest($crawler)) {
            $this->markTestSkipped('No hay equipos para listar');
            return;
        }

        $teamId = $this->getFirstTeamId($crawler);
        $lessonsAdminUrl = $container->get('router')->generate('lessons_team_admin', ['id' => $teamId]);
        $this->accessLessonsAdminPage($client, $lessonsAdminUrl);

        $this->deleteLesson($client, 'Lección de prueba',$lessonsAdminUrl);

        $crawler = $client->request('GET', $lessonsAdminUrl);
        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertLessonNotExists($crawler, 'Lección de prueba');
    }


    private function hasTeamsToTest(\Symfony\Component\DomCrawler\Crawler $crawler): bool
    {
        $tbody = $crawler->filter('table.table tbody');
        return $tbody->children()->count() > 0;
    }

    private function getFirstTeamId(\Symfony\Component\DomCrawler\Crawler $crawler): string
    {
        $teamId = $crawler->filter('table.table tbody tr:first-child a.btn')->first()->attr('href');
        return substr($teamId, strrpos($teamId, '/') + 1);
    }

    private function accessLessonsAdminPage(\Symfony\Bundle\FrameworkBundle\KernelBrowser $client, string $url): void
    {
        $client->request('GET', $url);
        $this->assertSame(200, $client->getResponse()->getStatusCode());
    }

    private function accessNewLessonPage(\Symfony\Bundle\FrameworkBundle\KernelBrowser $client, string $url): void
    {
        $client->request('GET', $url);
        $this->assertSame(200, $client->getResponse()->getStatusCode());
    }

    private function createNewLesson(\Symfony\Bundle\FrameworkBundle\KernelBrowser $client, string $lessonTitle): void
    {
        $crawler = $client->getCrawler();
        $form = $crawler->selectButton('Guardar')->form();
        $form['lesson[title]'] = $lessonTitle;
        $client->submit($form);

        $this->assertSame(302, $client->getResponse()->getStatusCode());
    }

    private function assertLessonExists(\Symfony\Component\DomCrawler\Crawler $crawler, string $lessonTitle): void
    {
        $lessonCount = $crawler->filter('table.table tbody tr td:contains("' . $lessonTitle . '")')->count();
        $this->assertGreaterThan(0, $lessonCount);
    }

    private function assertLessonNotExists(\Symfony\Component\DomCrawler\Crawler $crawler, string $lessonTitle): void
    {
        $lessonCount = $crawler->filter('table.table tbody tr td:contains("' . $lessonTitle . '")')->count();
        $this->assertSame(0, $lessonCount, 'La lección "' . $lessonTitle . '" no debería existir.');
    }


    private function deleteLesson(\Symfony\Bundle\FrameworkBundle\KernelBrowser $client, string $lessonTitle, String $lessonsAdminUrl): void
    {
        $crawler = $client->request('GET', $lessonsAdminUrl);
        $lessonId = $this->getLessonIdToDelete($crawler, $lessonTitle);
        $deleteButton = $crawler->filterXPath('//a[@class="btn btn-danger" and contains(@href, "'.$lessonId.'")]')->first();
        $deleteUrl = $deleteButton->attr('href');

        $client->request('GET', $deleteUrl);

        $this->assertSame(302, $client->getResponse()->getStatusCode());
    }

    private function getLessonIdToDelete(\Symfony\Component\DomCrawler\Crawler $crawler, string $lessonTitle): ?string
    {
        $lessonRow = $crawler->filter('table.table tbody tr')->reduce(function ($row) use ($lessonTitle) {
            return $row->filter('td')->first()->text() === $lessonTitle;
        });

        if ($lessonRow->count() > 0) {
            $deleteButton = $lessonRow->filter('a.btn-danger');
            $deleteUrl = $deleteButton->attr('href');
            $lessonId = basename($deleteUrl);
            return $lessonId;
        }

        return null;
    }



}
