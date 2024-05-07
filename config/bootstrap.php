
<?php

use DI\ContainerBuilder;
use Slim\App;

define('APP_ROOT', dirname(__DIR__));

require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once dirname(__DIR__) . '/app/constants/error.php';
require_once dirname(__DIR__) . '/app/constants/route.php';

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__, 1));
$dotenv->load();

$container = (new ContainerBuilder())
    ->addDefinitions(__DIR__ . '/container.php')
    ->build();

return $container->get(App::class);
