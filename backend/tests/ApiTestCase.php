<?php


namespace App\Tests;


use Doctrine\ORM\EntityManagerInterface;
use OAuth2\OAuth2;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;

abstract class ApiTestCase extends WebTestCase
{
    private ?string $token = null;

    /**
     * @var RouterInterface
     */
    protected ?RouterInterface $router = null;

    /**
     * @var KernelBrowser
     */
    private ?KernelBrowser $client = null;

    protected string $username = 'test';

    protected string $password = 'test';

    /**
     * @var EntityManagerInterface
     */
    protected EntityManagerInterface $em;

    public function setUp(): void
    {
        $this->client = self::createClient();
        $this->client->disableReboot();
        $this->router = self::$container->get(RouterInterface::class);
        $this->em = self::$container->get('doctrine.orm.default_entity_manager');

        $this->em->beginTransaction();
    }

    public function tearDown(): void
    {
        $this->em->rollback();
    }

    /**
     * @param string $url
     * @param string $method
     * @param array $parameters
     * @param array $files
     * @param array $headers
     *
     * @return Response
     */
    public function apiRequest(string $url, string $method = Request::METHOD_GET, array $parameters = [], array $files = [], array $headers = []): Response
    {
        $this->auth();

        $headers = array_merge([
            'HTTP_AUTHORIZATION' => $this->token,
        ], $headers);

        $this->client->request($method, $url, $parameters, $files, $headers);

        return $this->client->getResponse();
    }

    private function auth(): void
    {
        if ($this->token){
            return;
        }

        $url = $this->router->generate('fos_oauth_server_token');

        $parameters = [
            'client_id'     => '1_acrq817cptkw4s8osoo8k44os0gg00wwcgokso4840gw40sow',
            'client_secret' => '555bjdp8f18g0kwk8c4sssg8kcsk8wk840kooo4c408sc8cwwo',
            'response_type' => OAuth2::RESPONSE_TYPE_ACCESS_TOKEN,
            'grant_type'    => OAuth2::GRANT_TYPE_USER_CREDENTIALS,
            'username'      => $this->username,
            'password'      => $this->password,
        ];

        $this->client->request(Request::METHOD_POST, $url, $parameters);
        $response = $this->client->getResponse();
        $content = \json_decode($response->getContent(), true);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertArrayHasKey('access_token', $content);

        $this->token = $content['access_token'];
    }
}