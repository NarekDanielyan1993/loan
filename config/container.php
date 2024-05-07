<?php

use App\Controllers\LoanController;
use App\Handlers\HttpErrorHandler;
use App\Repositories\LoanRepository;
use App\Services\LoanService;
use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;
use Slim\App;
use Slim\Factory\AppFactory;

return [
    'database_config' => [
        'host' => $_ENV['DB_HOST'],
        'port' => $_ENV['DB_PORT'],
        'username' => $_ENV['DB_USERNAME'],
        'password' => $_ENV['DB_PASSWORD'],
        'database' => $_ENV['DB_NAME'],
    ],

    PDO::class => function (ContainerInterface $container) {
        $config = $container->get('database_config');
        $dsn = "pgsql:host={$config['host']};port={$config['port']};dbname={$config['database']}";

        $pdo = new PDO($dsn, $config['username'], $config['password']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        return $pdo;
    },

    LoanRepository::class => function (ContainerInterface $container) {
        $database = $container->get(PDO::class);

        return new LoanRepository($database);
    },

    LoanService::class => function (ContainerInterface $container) {
        $loanRepository = $container->get(LoanRepository::class);

        return new LoanService($loanRepository);
    },

    App::class => function (ContainerInterface $container) {
        $app = AppFactory::createFromContainer($container);

        $app->addBodyParsingMiddleware();
        $app->addRoutingMiddleware();
        (require __DIR__ . '/../config/routes.php')($app);

        $callableResolver = $app->getCallableResolver();
        $responseFactory = $app->getResponseFactory();
        $errorHandler = new HttpErrorHandler($callableResolver, $responseFactory);
        $errorMiddleware = $app->addErrorMiddleware(true, false, false);
        $errorMiddleware->setDefaultErrorHandler($errorHandler);

        return $app;
    },

    LoanController::class => function (ContainerInterface $container) {
        return new LoanController($container->get(LoanService::class));
    },
];
