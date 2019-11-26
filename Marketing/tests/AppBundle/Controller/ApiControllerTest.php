<?php

namespace Tests\AppBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Tests\WebTestCase;

class ApiControllerTest extends WebTestCase
{
    public function testRegionsAction()
    {
        $this->logIn();

        $this->client->request('GET', '/api/regions');
        $this->assertStatusCode(Response::HTTP_OK, $this->client);
    }
}
