<?php

namespace App\Tests\Controller;

use App\Repository\CommentRepository;
use App\Service\Enum\CommentState;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

class ConferenceControllerTest extends WebTestCase
{
    public function testIndex(): void
    {
        $client  = static::createClient();
        $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', 'Give your feedback');
    }

    public function testConferencePage()
    {
        $client  = self::createClient();
        $crawler = $client->request(Request::METHOD_GET, '/');

        $this->assertCount(2, $crawler->filter('h4'));
        $client->click($crawler->filter('h4 + p a')->link());

        $this->assertPageTitleContains('Amsterdam');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', 'Amsterdam 2019');
        $this->assertSelectorExists('div:contains("There are 1 comments")');
    }

    public function testCommentSubmission()
    {
        $client = self::createClient();
        $email  = 'me@automat.ed';
        $client->request(Request::METHOD_GET, 'conference/amsterdam-2019');
        $client->submitForm('Submit', [
            'comment_form[author]' => 'Fabien',
            'comment_form[text]' => 'Some feedback from an automated functional test',
            'comment_form[email]' => $email,
            'comment_form[photo]' => dirname(__DIR__, 2).'/public/images/under-construction.gif'
        ]);
        $this->assertResponseRedirects();
        // simulate comment validation
        $comment = self::getContainer()->get(CommentRepository::class)->findOneByEmail($email);
        $comment->setState(CommentState::PUBLISHED);
        self::getContainer()->get(EntityManagerInterface::class)->flush();

        $client->followRedirect();
        $this->assertSelectorExists('div:contains("There are 2 comments")');
    }
}
