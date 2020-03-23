<?php


namespace App\Tests;

use App\Service\MyService;
use Symfony\Component\HttpFoundation\Response;

/**
 * @group functional
 */
class TestControllerTest extends ApiTestCase
{
    public function testShouldAllowToInjectServiceMockToController(): void
    {
        $mock = $this->createMock(MyService::class);

        $mock
            ->expects($this->once())
            ->method('result')
            ->willReturn([
                'test' => 'my test string',
            ]);

        self::$container->set('test.my_service', $mock);

        $url = $this->router->generate('get_test');

        $response = $this->apiRequest($url);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);

        $this->assertEquals([
            'test' => 'my test string',
        ], $content);

    }
}

