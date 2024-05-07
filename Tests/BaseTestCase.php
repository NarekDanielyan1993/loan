<?php

namespace Tests;

use DI\ContainerBuilder;

use PHPUnit\Framework\TestCase;

use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Psr7\Environment;
use Slim\Psr7\Factory\StreamFactory;
use Slim\Psr7\Headers;
use Slim\Psr7\Request;
use Slim\Psr7\Uri;

abstract class BaseTestCase extends TestCase
{
    protected $app;

    protected function setUp(): void
    {
        parent::setUp();

        $container = (new ContainerBuilder())
            ->addDefinitions(APP_ROOT . '/config/container.php')
            ->build();

        $this->app = $container->get(App::class);

    }

    protected function getAppInstance()
    {
        return $this->app;
    }

    protected function runApp($request)
    {
        $response = $this->app->handle($request);
        return $response;
    }

    public function createRequest($method, $uri, $requestData = null, array $headers = ['HTTP_ACCEPT' => 'application/json', "Content-Type" => 'application/json'])
    {
        $env = Environment::mock([
            'REQUEST_METHOD' => $method,
            'REQUEST_URI' => $uri
        ]);

        $uri = new Uri('', '', 80, $uri, '');
        // $handle = fopen('php://temp', 'w+');
        // $stream = (new StreamFactory())->createStreamFromResource($handle);
        $h = new Headers();
        foreach ($headers as $name => $value) {
            $h->addHeader($name, $value);
        }
        
        $cookies = [];
        $serverParams = [];


        $streamFactory = new StreamFactory();
        $body = $streamFactory->createStream('');

        if ($requestData !== null) {
            $body->write(json_encode($requestData));
        }

        $uploadedFiles = [];

        return new Request($method, $uri, $h, $cookies, $serverParams, $body, $uploadedFiles);
    }
}
