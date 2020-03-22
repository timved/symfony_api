<?php

namespace App\Tests;

use OAuth2\OAuth2;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;

class AuthorizationTest extends WebTestCase
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var KernelBrowser
     */
    private $client;

    public function setUp()
    {
        $this->client = self::createClient();
        $this->router = self::$container->get(RouterInterface::class);
    }

    public function testAuthorization()
    {
        $url = $this->router->generate('fos_oauth_server_token');

        $parameters = [
            'client_id'     => '1_acrq817cptkw4s8osoo8k44os0gg00wwcgokso4840gw40sow',
            'client_secret' => '555bjdp8f18g0kwk8c4sssg8kcsk8wk840kooo4c408sc8cwwo',
            'response_type' => OAuth2::RESPONSE_TYPE_ACCESS_TOKEN,
            'grant_type'    => OAuth2::GRANT_TYPE_USER_CREDENTIALS,
            'username'      => 'test',
            'password'      => 'test',
        ];

        $this->client->request(Request::METHOD_POST, $url, $parameters);
        $response = $this->client->getResponse();
        $content = \json_decode($response->getContent(), true);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());


        $url = $this->router->generate('get_test');

        $this->client->request(Request::METHOD_GET, $url, [], [], [
            'HTTP_AUTHORIZATION' => $content['access_token'],
        ]);
        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_NO_CONTENT, $response->getStatusCode());
        $content = \json_decode($response->getContent(), true);
//        $this->assertEquals(Response::HTTP_FORBIDDEN, $response->getStatusCode());
//        $this->assertEquals('Test Access Denied.', $content['message']);
//        $this->assertEquals(Response::HTTP_FORBIDDEN, $content['code']);

        return;

    }
}