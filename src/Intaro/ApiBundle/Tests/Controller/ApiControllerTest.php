<?php

namespace Intaro\ApiBundle\Tests\Controller;

use Liip\FunctionalTestBundle\Test\WebTestCase;

class ApiControllerTest extends WebTestCase
{
    public function testAddAction()
    {
        $this->client = static::createClient();
        $this->client->request(
            'POST', 
            '/api/v1/books/add',  
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            '{"title":"title1","author":"body1"}'
        );
        
        $this->assertJsonResponse($this->client->getResponse(), 201, false);
    }
}
